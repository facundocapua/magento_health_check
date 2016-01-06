<?php
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

if (isset($_POST['submit'])) {
    shell_exec('rm -rf ./tmp');
    mkdir('./tmp');
    $fileToTest = './tmp/' . $_FILES['file']['name'];
    if (move_uploaded_file($_FILES['file']['tmp_name'], $fileToTest)) {
        shell_exec("cd tmp/ && unzip " . $_FILES['file']['name']);

        $mageFile = rsearch('./tmp/', '/Mage\.php/');
        if ($mageFile) {
            include $mageFile;
            $mageVersion = Mage::getVersionInfo();
            $mageEdition = Mage::getEdition();

            if ($mageEdition == Mage::EDITION_COMMUNITY) {
                $vanillaVersion = 'CE-';
            } else {
                $vanillaVersion = 'EE-';
            }
            $vanillaVersion .= $mageVersion['major'] . '.' . $mageVersion['minor'] . '.' . $mageVersion['revision'] . '.' . $mageVersion['patch'];

            if (!file_exists('./vanillas/' . $vanillaVersion)) {
                throw new RuntimeException(sprintf('The version %s is not available for comparison!', $vanillaVersion));
            }

            $mageRootFolder = realpath(dirname($mageFile) . '/../');
            $vanillaMagentoFolder = realpath("./vanillas/" . $vanillaVersion);
            $cmd = "diff -urbB " . $vanillaMagentoFolder . "  " . $mageRootFolder . " | lsdiff";
            $result = shell_exec($cmd);
            $result = str_replace($mageRootFolder, '', $result);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Magento Project Checker</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"
          integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1>Magento Project Checker
                <small>by Summa Solutions</small>
            </h1>
            <?php if (isset($_POST['submit'])): ?>
                <?php if (!empty($cmd)): ?>
                    <strong>Command executed:</strong>
                    <pre><?php echo $cmd; ?></pre><br/>
                <?php endif; ?>
                <?php if (empty($result)): ?>
                    <p class="bg-success">Good job!! No diffs with core files =)</p>
                <?php else: ?>
                    <pre><?php echo nl2br($result); ?></pre>
                <?php endif; ?>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="file">Upload your project files</label>
                    <input type="file" name="file" id="file"/>
                    <span id="helpBlock" class="help-block">Allowed extensions: .tgz, .zip</span>
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Check!</button>
            </form>

        </div>
    </div>
</div>
</body>
</html>
