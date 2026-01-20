<?php

namespace App\Controller;

use App\Entity\Form;
use App\Entity\FormSubmission;
use App\Repository\FormRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class FormSubmissionController extends AbstractController
{
    public function __construct(
        private FormRepository $formRepository,
        private EntityManagerInterface $em,
        private MailerInterface $mailer
    ) {}

    #[Route('/form/submit/{id}', name: 'form_submit', methods: ['POST'])]
    public function submit(Form $form, Request $request): Response
    {
        $data = $request->request->all();
        
        // Honeypot-Spam-Schutz
        if (!empty($data['_hp'])) {
            return $this->redirectToRoute('page_show', ['slug' => $request->headers->get('referer')]);
        }
        
        unset($data['_hp'], $data['_token']);
        
        // Validierung
        $errors = [];
        foreach ($form->getFields() as $field) {
            if ($field->isRequired() && empty($data[$field->getName()])) {
                $errors[] = $field->getLabel() . ' ist ein Pflichtfeld.';
            }
            
            // E-Mail-Validierung
            if ($field->getType() === 'email' && !empty($data[$field->getName()])) {
                if (!filter_var($data[$field->getName()], FILTER_VALIDATE_EMAIL)) {
                    $errors[] = $field->getLabel() . ' muss eine gültige E-Mail-Adresse sein.';
                }
            }
        }
        
        if (!empty($errors)) {
            $this->addFlash('form_error', implode('<br>', $errors));
            return $this->redirect($request->headers->get('referer'));
        }
        
        // Submission speichern
        if ($form->isStoreSubmissions()) {
            $submission = new FormSubmission();
            $submission->setForm($form);
            $submission->setData($data);
            $submission->setIpAddress($request->getClientIp());
            $submission->setUserAgent($request->headers->get('User-Agent'));
            
            $this->em->persist($submission);
            $this->em->flush();
        }
        
        // E-Mail versenden
        $this->sendNotificationEmail($form, $data);
        
        $this->addFlash('form_success', $form->getSuccessMessage());
        return $this->redirect($request->headers->get('referer'));
    }

    private function sendNotificationEmail(Form $form, array $data): void
    {
        $body = "Neue Formular-Einsendung: " . $form->getName() . "\n\n";
        
        foreach ($form->getFields() as $field) {
            $value = $data[$field->getName()] ?? '-';
            $body .= $field->getLabel() . ": " . $value . "\n";
        }
        
        $email = (new Email())
            ->from('noreply@' . $_SERVER['HTTP_HOST'] ?? 'localhost')
            ->to($form->getRecipient())
            ->subject($form->getSubject())
            ->text($body);
        
        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log error but don't fail submission
            error_log('Form email failed: ' . $e->getMessage());
        }
    }
}
