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
        }
    }
}
?>

<html>
<head>
    <title>Project Checker</title>
</head>
<body>
<h1>Project Checker</h1>
<?php if (!empty($result)): ?>
    <strong>Command executed:</strong> <pre><?php echo $cmd; ?></pre><br />
    <?php echo nl2br($result); ?>
<?php else: ?>
    <form method="post" enctype="multipart/form-data">
        Upload your project files
        Allowed extensions: .tgz, .zip
        <input type="file" name="file"/>
        <button type="submit" name="submit">Check!</button>
    </form>
<?php endif; ?>
</body>
</html>
