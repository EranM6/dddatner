<?php

function getAllScriptFiles($root, $dir, $fileList) {

	$files = scandir($root . $dir);
	forEach ($files as $file) {
		if ($file[0] != ".") {
			if (is_dir($root . $dir . '/' . $file)) {
				$fileList = getAllScriptFiles($root, $dir . '/' . $file, $fileList);
			} else {
				if (pathinfo($file)['extension'] == "js") {
					array_push($fileList, './' . $dir . '/' . $file);
				}
			}
		};
	}
	return $fileList;
}