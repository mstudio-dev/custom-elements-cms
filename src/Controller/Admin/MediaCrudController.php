<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Symfony\Component\HttpFoundation\Response;
use Vich\UploaderBundle\Form\Type\VichImageType;

class MediaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Media::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Medium')
            ->setEntityLabelInPlural('Mediathek')
            ->setDefaultSort(['type' => 'DESC', 'uploadedAt' => 'DESC'])
            ->setSearchFields(['name', 'description', 'alt', 'mimeType', 'path'])
            ->setPaginatorPageSize(30)
            ->showEntityActionsInlined()
            ->setPageTitle('index', 'Mediathek')
            ->setPageTitle('new', 'Medium hochladen')
            ->setPageTitle('edit', 'Medium bearbeiten')
            ->overrideTemplate('crud/index', 'admin/media_index.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        $moveToFolder = Action::new('moveToFolder', 'Verschieben', 'fa fa-folder-open')
            ->linkToCrudAction('moveToFolder')
            ->displayAsLink()
            ->setCssClass('btn btn-sm btn-info');
        
        $batchMove = Action::new('batchMoveToFolder', 'Ausgewählte verschieben')
            ->linkToCrudAction('batchMoveToFolder')
            ->addCssClass('btn btn-primary')
            ->setIcon('fa fa-folder-open');

        return $actions
            ->add(Crud::PAGE_INDEX, $moveToFolder)
            ->addBatchAction($batchMove);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('type', 'Typ')->setChoices([
                'Ordner' => 'folder',
                'Datei' => 'file'
            ]))
            ->add(EntityFilter::new('parent', 'Übergeordneter Ordner'))
            ->add(ChoiceFilter::new('mimeType', 'Dateityp')->setChoices([
                'Bilder (JPG/PNG)' => 'image/jpeg|image/png',
                'PDFs' => 'application/pdf',
                'Videos' => 'video/mp4|video/mpeg',
                'Dokumente' => 'application/msword|application/vnd.openxmlformats-officedocument',
            ]))
            ->add('uploadedAt', 'Hochgeladen')
            ->add('size', 'Dateigröße');
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        
        // Optionaler Filter: Zeige nur Root-Elemente wenn kein Ordner-Filter aktiv ist
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $folderId = $request->query->get('folder');
        
        if ($folderId === null && !$filters->all()) {
            // Nur Breadcrumb-Navigation, keine automatische Filterung mehr
        }
        
        return $qb;
    }

    public function moveToFolder(AdminContext $context, EntityManagerInterface $em): Response
    {
        $media = $context->getEntity()->getInstance();
        $request = $this->container->get('request_stack')->getCurrentRequest();
        
        if ($request->isMethod('POST')) {
            $folderId = $request->request->get('target_folder');
            $targetFolder = $folderId ? $em->getRepository(Media::class)->find($folderId) : null;
            
            $media->setParent($targetFolder);
            $em->flush();
            
            $this->addFlash('success', 'Medium wurde verschoben.');
            
            return $this->redirect($context->getReferrer() ?? $this->generateUrl('admin', [
                'crudAction' => 'index',
                'crudControllerFqcn' => MediaCrudController::class
            ]));
        }
        
        $folders = $em->getRepository(Media::class)->findAllFolders();
        
        return $this->render('admin/media_move.html.twig', [
            'media' => $media,
            'folders' => $folders,
        ]);
    }

    public function batchMoveToFolder(AdminContext $context, EntityManagerInterface $em): Response
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $entityIds = $request->request->all('batchActionEntityIds') ?? [];
        $folderId = $request->request->get('target_folder');
        
        if ($request->isMethod('POST') && $folderId !== null && !empty($entityIds)) {
            $targetFolder = $folderId ? $em->getRepository(Media::class)->find($folderId) : null;
            
            foreach ($entityIds as $id) {
                $media = $em->getRepository(Media::class)->find($id);
                if ($media) {
                    $media->setParent($targetFolder);
                }
            }
            
            $em->flush();
            
            $this->addFlash('success', count($entityIds) . ' Medien wurden verschoben.');
            
            $referrer = $request->request->get('referrer') ?? $this->generateUrl('admin', [
                'crudAction' => 'index',
                'crudControllerFqcn' => MediaCrudController::class
            ]);
            
            return $this->redirect($referrer);
        }
        
        $folders = $em->getRepository(Media::class)->findAllFolders();
        
        return $this->render('admin/media_batch_move.html.twig', [
            'entity_ids' => $entityIds,
            'folders' => $folders,
            'referrer' => $request->headers->get('referer'),
        ]);
    }

    public function moveFileToFolder(AdminContext $context, EntityManagerInterface $em): Response
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $data = json_decode($request->getContent(), true);
        
        $fileId = $data['fileId'] ?? null;
        $folderId = $data['folderId'] ?? null;
        
        if ($fileId && $folderId) {
            $file = $em->getRepository(Media::class)->find($fileId);
            $folder = $em->getRepository(Media::class)->find($folderId);
            
            if ($file && $folder && $folder->getType() === 'folder') {
                $file->setParent($folder);
                $em->flush();
                
                return $this->json(['success' => true, 'message' => 'Datei verschoben']);
            }
        }
        
        return $this->json(['success' => false, 'message' => 'Fehler beim Verschieben'], 400);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm();

        yield TextField::new('name', 'Name')
            ->setRequired(true)
            ->formatValue(function ($value, $entity) {
                return $entity->getIcon() . ' ' . $entity->getName();
            })
            ->hideOnForm();
        
        yield TextField::new('name', 'Dateiname')
            ->setRequired(true)
            ->onlyOnForms();
        
        yield ChoiceField::new('type', 'Typ')
            ->setChoices([
                'Datei' => 'file',
                'Ordner' => 'folder'
            ])
            ->setRequired(true)
            ->renderAsNativeWidget()
            ->onlyOnForms();

        yield AssociationField::new('parent', 'Ordner')
            ->setRequired(false)
            ->formatValue(function ($value, $entity) {
                if (!$value) return '📁 Root';
                return '📁 ' . $value->getFullPath();
            })
            ->setFormTypeOption('query_builder', function ($repository) {
                return $repository->createQueryBuilder('m')
                    ->where('m.type = :type')
                    ->setParameter('type', 'folder')
                    ->orderBy('m.name', 'ASC');
            });

        // Upload-Feld nur für Dateien, nicht für Ordner
        yield Field::new('file', 'Datei')
            ->setFormType(VichImageType::class)
            ->hideOnIndex()
            ->setRequired(false);

        // Vorschau nur für hochgeladene Bilder
        yield ImageField::new('path', 'Vorschau')
            ->setBasePath('/uploads')
            ->onlyOnIndex();
        
        yield TextField::new('mimeType', 'MIME-Typ')
            ->onlyOnIndex();

        yield IntegerField::new('size', 'Größe')
            ->formatValue(function ($value) {
                if (!$value) return '-';
                if ($value < 1024) return $value . ' B';
                if ($value < 1048576) return round($value / 1024, 2) . ' KB';
                return round($value / 1048576, 2) . ' MB';
            })
            ->onlyOnIndex();

        yield TextField::new('alt', 'Alt-Text')
            ->setHelp('Alternativer Text für Bilder (wichtig für Barrierefreiheit)')
            ->setRequired(false)
            ->hideOnIndex();

        yield TextareaField::new('description', 'Beschreibung')
            ->setRequired(false)
            ->hideOnIndex();

        yield IntegerField::new('sorting', 'Sortierung')
            ->setHelp('Niedrigere Zahlen erscheinen zuerst')
            ->setRequired(false)
            ->hideOnIndex();

        yield DateTimeField::new('uploadedAt', 'Hochgeladen am')
            ->hideOnForm();

        yield DateTimeField::new('updatedAt', 'Aktualisiert am')
            ->hideOnForm()
            ->hideOnIndex();
    }
}
