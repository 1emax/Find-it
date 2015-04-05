<?php
headers();
if (isset($_POST['ajax']) && $_POST['ajax'] != '') {
	$find = new FindIt($_POST['ajax']['find']);
} else {
	wrap_in_html();
}


function wrap_in_html() {
	echo '<html><head><title>Find it</title>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
	<script src="//life.mywat.ru/findit.js"></script>
	</head>	<body>';


	echo ini_get('max_execution_time');
	echo '<br>' . return_bytes(ini_get('memory_limit'));
	$find = new FindIt(json_encode('function getArrayKey'));
	$find->searchDir = '.';
	$find->init();
	$find->allToCheck();
	// $dir = './classes/system';// /templates/bigtorg';

	// $findtext = 'getFieldTypeByDataType'; // b-f-left 
	// echo "Строка - |" . $findtext . '|<br>';
	// $start = microtime(true);
	// $allUnits = openAllDir($dir);
	// $time = microtime(true) - $start;
	// echo $time;
	// // printf('Скрипт выполнялся %.4F сек.', $time);

	// if (is_array($allUnits) && count($allUnits) > 0) {
	// 	foreach ($allUnits as $value) {
	// 		// echo '<br>' . $value . ' содержит';
	// 	}
	// } else {
	// 	echo 'Результаты по запросу |' . $findtext . '| - отсутствуют.';
	// }

	// allToCheck($allUnits, $findtext);

	echo '</body></html>';

}

class FindIt
{
	public $findtext = '';
	public $searchDir = '.';
	public $files = array();
	public $extension = '';
	protected $additionFind = false;

	/* add:
	function to detect regular or not query text. Get type of it, than file content, than check matches
	function 'without dirs'
	function 'only dirs' (also add current)
	function 'giveMeFileContent'
	add cms detection, arrays with extra cms files and dirs
	function for notifications, where script was runed. Also function for checking if script still exist  on site
	*/

	public function __construct($findtext) {
		$findQuery = json_decode($findtext, true);
		if (is_null($findQuery)) die(json_encode('incorrect query string'));
		// $type = gettype($findQuery);
		if (is_string($findQuery)) {
			$this->findtext = $findQuery;

		} elseif (is_array($findQuery)) {
			$this->findtext = $findQuery[0];
			unset($findQuery[0]);
			$this->additionFind = $findQuery;
		} 
	}

	public function openAllDir($directory = false, $fileList = array()) {
		if ($directory == false) {
			$directory = preg_replace('/\/$/', '', $this->searchDir);
		}

	 	$dir = opendir($directory) or die('Не могу открыть папку');
	 	while ($file = readdir($dir)){
	  		if ($file != '.' && $file != '..' && $file != 'Thumbs.db' && $file != 'error_log' &&  $file != 'changeit.php') {
	  			$full_path = $directory.'/'.$file;
				if (is_dir($full_path)) {
	  				$fileList[$file] = $this->openAllDir($full_path, $fileList[$file]);
	  			} elseif (is_file($full_path)) {
	  				  	$fileList['dir_files_list'][] = array( 'path' => $full_path, 'filesize' => filesize($full_path), 'last_modified'=>filemtime($full_path) );
				}
			}
		}
		return $fileList;
	}

	public function init() {
		// $this->searchDir = '.';
		$this->files = $this->openAllDir();
		// print_r($list);
		// foreach ($list as $key => $value) {
		// 	echo '<br>' . $key . ': ' . $value;
		// }
	}

	public function thisContainText($where) {
		// echo '<br>'.$where . ' i has '.$what.' <br>';
		$pos = strpos(file_get_contents($where), $this->findtext);
		if ($pos === false) {
			return false;
		} else {
			return true;
		}
	}

	public function allToCheck($filesArr = false) {
		if ($filesArr == false) {
			$filesArr = $this->files;
		}

		foreach ($filesArr as $el) {
			if (is_array($el)) {
				$this->allToCheck($el);
			} elseif ($this->thisContainText($el)) {
				echo '<br>' . $el . ' содержит.';
			}
		}
	}

	// Принимает список файлов для фильтра, возвращается список файлов с соответствующим расширением файла
	public function onlyWithExtension($fileList = false) {
		if ($neeDeeper == false) {
			$fileList = $this->files;
		}

		$files = $fileList['dir_files_list'];
		unset($fileList['dir_files_list']);

		foreach ($files as $fileName ) {
			if (preg_match('/' . $this->extension . '$/', $fileName)) {
				$fileList['dir_files_list'][] = $fileName;
			}			
		}

		foreach ($fileList as $dir => $content) {
			if ($dir == 'dir_files_list') continue;
			$fileList[$dir] = $this->onlyWithExtension($content);
		}

		return $fileList;
	}
}










function replaceInFile($file, $what, $to) {
	$file_string = file_get_contents($file);
	$changed_str = str_replace($what, $to, $file_string);
	$fo = fopen($file, 'w+');
	fwrite($fo, $changed_str);
	fclose($fo);
}

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last) {
        // Модификатор 'G' доступен, начиная с PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

function headers() {
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// дата в прошлом
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  // всегда модифицируется
	header("Cache-Control: no-store, no-cache, must-revalidate");// HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header('Content-Type: text/html; charset=utf-8');
	header("Pragma: no-cache");// HTTP/1.0
}

?>