<?php

namespace App\AssetMapper;

use ScssPhp\ScssPhp\Compiler;
use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\AssetMapper\Compiler\AssetCompilerInterface;
use Symfony\Component\AssetMapper\MappedAsset;

/**
 * Compiles SCSS files to CSS using scssphp
 */
class ScssCompiler implements AssetCompilerInterface
{
    public function supports(MappedAsset $asset): bool
    {
        return str_ends_with($asset->logicalPath, '.scss');
    }

    public function compile(string $content, MappedAsset $asset, AssetMapperInterface $assetMapper): string
    {
        $compiler = new Compiler();
        
        // Set import paths for @import resolution
        $compiler->addImportPath(dirname($asset->sourcePath));
        
        // Add parent directories for relative imports
        $basePath = dirname($asset->sourcePath);
        while ($basePath !== dirname($basePath)) {
            $compiler->addImportPath($basePath);
            $basePath = dirname($basePath);
        }
        
        try {
            $result = $compiler->compileString($content);
            return $result->getCss();
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf(
                'Error compiling SCSS file "%s": %s',
                $asset->logicalPath,
                $e->getMessage()
            ), 0, $e);
        }
    }
}
