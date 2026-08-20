<?php

declare(strict_types=1);

if (ini_get("phar.readonly")) {
	fwrite(STDERR, "phar.readonly is enabled in php.ini. Run with: php -d phar.readonly=0 make_phar.php\n");
	exit(1);
}

$root = __DIR__;
$pluginYmlPath = $root . "/plugin.yml";
$pluginYml = file_get_contents($pluginYmlPath);
if ($pluginYml === false) {
	fwrite(STDERR, "Could not read $pluginYmlPath\n");
	exit(1);
}

if (!preg_match('/^name:\s*(\S+)/m', $pluginYml, $nameMatch) || !preg_match('/^version:\s*(\S+)/m', $pluginYml, $versionMatch)) {
	fwrite(STDERR, "Could not find name/version in plugin.yml\n");
	exit(1);
}
$name = $nameMatch[1];
$version = $versionMatch[1];

$buildDir = $root . "/build";
if (!is_dir($buildDir) && !mkdir($buildDir, 0777, true) && !is_dir($buildDir)) {
	fwrite(STDERR, "Could not create $buildDir\n");
	exit(1);
}

$outFile = "$buildDir/{$name}_v{$version}.phar";
if (file_exists($outFile)) {
	unlink($outFile);
}

/**
 * @return list<string>
 */
function collectFiles(string $dir) : array {
	if (!is_dir($dir)) {
		return [];
	}
	$files = [];
	$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
	foreach ($iterator as $file) {
		if ($file->isFile()) {
			$files[] = $file->getPathname();
		}
	}
	return $files;
}

$phar = new Phar($outFile);
$phar->setStub("<?php __HALT_COMPILER();");
$phar->startBuffering();

$phar->addFile($pluginYmlPath, "plugin.yml");

foreach (["resources", "src"] as $topLevelDir) {
	$base = "$root/$topLevelDir";
	foreach (collectFiles($base) as $file) {
		$relative = str_replace("\\", "/", substr($file, strlen($base) + 1));
		$phar->addFile($file, "$topLevelDir/$relative");
	}
}

$phar->stopBuffering();
$phar->compressFiles(Phar::GZ);

echo "Built $outFile\n";
