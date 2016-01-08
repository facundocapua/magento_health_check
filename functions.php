<?php
function getExtractCommand($file)
{
    $extractCmd = '';
    switch($file['type']){
        case 'application/zip':
            $extractCmd = 'unzip';
            break;
        case 'application/x-tar':
            $extractCmd = 'tar -zxf';
            break;
    }

    return $extractCmd;
}

function getAppliedPatches($appliedPatchesFile)
{
    $appliedPatches = [];
    $content = file_get_contents($appliedPatchesFile);
    $lines = explode("\n", $content);
    foreach($lines as $line){
        if(strpos($line, '|') !== false){
            $data = explode('|', $line);
            $appliedPatches[] = trim($data[1]);
        }
    }

    return $appliedPatches;
}

function getPatchFile($appliedPatch, $mageEdition, $mageVersion)
{
    if(file_exists('./vanillas/patches/PATCH_'.$appliedPatch.'_'.$mageEdition.'_'.$mageVersion.'.sh')){
        $patchFile = 'PATCH_'.$appliedPatch.'_'.$mageEdition.'_'.$mageVersion.'.sh';
    }elseif(file_exists('./vanillas/patches/PATCH_'.$appliedPatch.'_'.$mageEdition.'.sh')){
        $patchFile = 'PATCH_'.$appliedPatch.'_'.$mageEdition.'.sh';
    }elseif(file_exists('./vanillas/patches/PATCH_'.$appliedPatch.'.sh')){
        $patchFile = 'PATCH_'.$appliedPatch.'.sh';
    }else{
        $patchFile = null;
    }

    return $patchFile;
}

function rsearch($folder, $pattern)
{
    $dir = new RecursiveDirectoryIterator($folder);
    $ite = new RecursiveIteratorIterator($dir);
    foreach ($ite as $file) {
        if (preg_match($pattern, $file->getPathName())) {
            return $file->getPathName();
        }
    }

    return false;
}
