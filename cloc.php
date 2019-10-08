<?php
/**
 *统计指定目录下文件总行数
 *@link https://blog.csdn.net/oushunbao/article/details/84794069
 *
 */
class CountCode {

	/**
	 * 统计当前文件有多少行代码，
	 * @return CountCodeInfo
	 */
	public function countByFile($fullFileName) {
		$fileContent = file_get_contents($fullFileName);
		$lines       = explode("\n", $fileContent);
		$lineCount   = count($lines);

		for ($i = $lineCount - 1; $i > 0; $i -= 1) {
			$line = $lines[$i];
			if ($line != "") {
				break;
			}

			$lineCount -= 1; //最后几行是空行的要去掉。
		}
		unset($fileContent);
		unset($lines);

		$countCodeInfo = new CountCodeInfo();
		$countCodeInfo->setFileCount(1);
		$countCodeInfo->setLineCount($lineCount);
		return $countCodeInfo;
	}

	/**
	 * 统计当前目录下（含子目录）
	 * 有多少文件，以及多少行代码
	 *
	 * countInfo = array( "fileCount"=>?, "lineCount"=>? );
	 *
	 * @return CountCodeInfo
	 */
	public function countByDir($dirName) {
		$fileList     = scandir($dirName);
		$countCodeDir = new CountCodeInfo();
		foreach ($fileList as $fileName) {
			// 排除以.开通的文件
			if (0 === strpos($fileName, '.')) {
				continue;
			}

			// 排除图像
			$extension = strtolower(substr(strrchr($fileName, '.'), 1));
			if (in_array($extension, array('jpg', 'png', 'gif', 'jpeg', 'bmp'))) {
				continue;
			}

			$fullFileName = $dirName . "/" . $fileName;
			if (is_file($fullFileName)) {
				$countCodeSub = $this->countByFile($dirName . "/" . $fileName);
			} else if (is_dir($fullFileName)) {
				$countCodeSub = $this->countByDir($dirName . "/" . $fileName);
			} else {
				$countCodeSub = new CountCodeInfo();
			}

			$countCodeDir->increaseByOther($countCodeSub);
		}
		return $countCodeDir;
	}

	public function countByDirOrFile($dirOrFileName) {
		if (is_dir($dirOrFileName)) {
			return $this->countByDir($dirOrFileName);
		} else if (is_file($dirOrFileName)) {
			return $this->countByFile($dirOrFileName);
		} else {
			return new CountCodeInfo();
		}
	}

	public function count($dirList) {
		$countCodeAll = new CountCodeInfo();
		foreach ($dirList as $dirName) {
			$countCodeSub = $this->countByDirOrFile($dirName);
			$countCodeAll->increaseByOther($countCodeSub);
		}
		return $countCodeAll;
	}

}

class CountCodeInfo {
	private $fileCount = 0;
	private $lineCount = 0;

	public function getFileCount() {
		return $this->fileCount;
	}

	public function getLineCount() {
		return $this->lineCount;
	}

	public function setFileCount($fileCount) {
		$this->fileCount = $fileCount;
		return $this;
	}

	public function setLineCount($lineCount) {
		$this->lineCount = $lineCount;
		return $this;
	}

	/**
	 * 累加
	 */
	public function increaseByOther($countCodeInfo) {
		$this->setFileCount($this->fileCount + $countCodeInfo->getFileCount());
		$this->setLineCount($this->lineCount + $countCodeInfo->getLineCount());
		return $this;
	}
}
