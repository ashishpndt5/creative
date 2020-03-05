<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ReceiveMessageWFController;


use App\Http\Controllers\AppConfigController;
use Log;
use Storage;
use File;
use Config;

class FileProcessorController extends Controller
{
    public $filePath;
    public function __construct() {

    }

    public static function boot($trader,$partner) {
    	
    	if($trader == 0){
    		Log::warning("FileProcessorController boot : Invalid trader: " . $trader);
    	}
    	if($partner == 0){
    		Log::warning("FileProcessorController boot : Invalid partner: " . $partner);
    	}
    	//$this->execute($trader,$partner);
    	//self::execute($trader,$partner);
    	return (new self)->execute($trader,$partner);
    }
    
    public function execute($traderId =  0, $partnerId = 0) {
    	
    	$traderFound = 0;
    	$partnerFound = 0;
    	$filePath = $traderId. DIRECTORY_SEPARATOR . $partnerId;
    	
    	Config::set('filesystems.trader_id', $traderId);
    	Config::set('filesystems.partner_id', $partnerId);
    	
    	$dir_path = storage_path() .  DIRECTORY_SEPARATOR .'app'. DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'data';
    	$traders = $this->getDirContentsNew($dir_path);
    	
    	foreach($traders as $trader) {
    		
    		if($trader != $traderId) continue;
    		$traderFound = 1;
    		$partner = $dir_path . DIRECTORY_SEPARATOR . $trader;
    		$partners = $this->getDirContentsNew($partner);
    		foreach($partners as $partner) {
    			
    			if(((string) $partnerId != "0") && ((string) $partner != (string) $partnerId)) continue;
    			$this->processPartnerInFiles("in", "processed", $traderId, $partner);
    		}
    	}
    }
    
    private function getDirContents() {
    	
    	$directories = array();
    	$directories = Storage::disk('public')->directories();
    	$root = Storage::disk('public')->files();
    	
    	$dir_path = storage_path() .  DIRECTORY_SEPARATOR .'app'. DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'data';
    	$directoriess = Storage::files($dir_path);
    	foreach ($dir as $fileinfo) {
    		if (!$fileinfo->isDot()) {
    	
    		}
    		else {
    	
    		}
    	}
    	
    	$directory = '1';  
		$files = Storage::disk('public')->files($directory, true);
    	
    	$dr1 = Storage::disk('public')->getDriver();
    	$dr2 = Storage::disk('public')->getDriver()->getAdapter();
    	$dr3 = Storage::disk('public')->getDriver()->getAdapter()->getPathPrefix()->directories();
    	//$directories = Storage::disk('public/data')->directories();
    	$fileNamePathIn = '1'. DIRECTORY_SEPARATOR .'2'. DIRECTORY_SEPARATOR . 'in' ;
    	$ssd = Storage::disk('public')->directories($fileNamePathIn);
    	$directoriesF = Storage::files('public/5/1/in');
    	if(count($directories) > 0) {
    		return $directories;	
    	}
    	/*$path = public_path('data/5/1');
    	$files = File::allfiles($path);
    	foreach($directories as $path) {
    		//$file = pathinfo($path);
    		$ss = $path;
    		$resp[] = $path;
    		//$resp[] = $file['basename'];
    		//echo $file['filename'] ;
    	}
    	return $resp;
    	*/
    	    	
    /*......................................................................*/	
    	$path = public_path('uploads');
    	 
    	if(!File::isDirectory($path)){
    		File::makeDirectory($path, 0777, true, true);
    	} else {
    		$files = File::allfiles($path);
    		//$nam = $files->SplFileInf;
    		foreach($files as $path) {
    			$file = pathinfo($path);
    			$resp[] = $file['basename'];
    		}
    	}
   
    	if(!$resp){
    		//$this->logger->LogError("empty folder: " . $filePath . "/" . $source );
    		//Log::warning("FileProcessorController boot : Invalid partner: " . $partner);
    		return;
    	}
    	return $resp;
    }
    
    private function getDirContentsNew($path)
    {
    	if(!is_dir($path)) return;
    	$resp = null;
    	if ($phandle = opendir($path)) {
    		while (false !== ($dirContents = readdir($phandle))) {
    			if ($dirContents != "." && $dirContents != "..") {
    				$resp[] = $dirContents;
    			}
    		}
    		closedir($phandle);
    	}
    	return $resp;
    }
    
    private function getDirFiles($path, $trader, $partner, $source) {
    	$resp = array();
    	//$path = public_path('uploads');
    	//$files = File::allfiles($path);
    	//$files = public_path('data/5/1/in');
    	//$files = Storage::files('public/data/5/1/in');
    	$path = $path. $trader. DIRECTORY_SEPARATOR .$partner. DIRECTORY_SEPARATOR .$source;
    	$files = Storage::files($path);
    	//$files = File::allfiles($path);
    	//$file = File::allfiles($path);
    	foreach($files as $file) {
    		$file = pathinfo($file);
    		$resp[] = $file['basename'];
    	}
    	if(count($resp) > 0 && $resp[0] != '') {
    		return $resp;
    	}
    	return false;
    	
    }
    
    private function processPartnerInFiles($source, $dest, $trader_id, $partner_id) {
    	//$config = Config::get('config.path');
    	//$cc = Config::get('filesystems.path');
    	$path = 'public'. DIRECTORY_SEPARATOR .'data'. DIRECTORY_SEPARATOR;
    	
    	$ipFiles = $this->getDirFiles($path, $trader_id, $partner_id, $source);
    	//return;
    	if(!$ipFiles) {
    		//$this->logger->LogError("empty folder: " . $filePath . "/" . $source );
    		Log::notice("empty folder: " . $path . $trader_id. DIRECTORY_SEPARATOR .$partner_id. DIRECTORY_SEPARATOR .$source );
    		return;
    	}
    
    	foreach($ipFiles as $file) {
    		if(is_dir($file)) continue; //not an input file - processed/transmitted folders
    		//$workflow = new receiveMessageWF($this->logger);
    		$rcwf = new ReceiveMessageWFController();
    		$rcwf->setTraderId($trader_id);
    		$rcwf->setPartnerId($partner_id);
    		
    		$response = $rcwf->execute($file);
    		
    		//$ipFileName = $filePath . "/" . $source . "/" . $file;
    		//$destFileName = $filePath . "/" . $dest . "/" . $file;
    		//$this->logger->LogDebug("calling workflow execute " . $ipFileName);
    		//echo "processing: " . $ipFileName . "\n";
    		//$response=$workflow->execute($trader, $partner, $ipFileName);
    		$fpath = $path. $trader_id. DIRECTORY_SEPARATOR .$partner_id. DIRECTORY_SEPARATOR .$source. DIRECTORY_SEPARATOR .$file;
    		$ipFileName = $fpath;
    		$destFileName = $path. $trader_id. DIRECTORY_SEPARATOR .$partner_id. DIRECTORY_SEPARATOR .$dest. DIRECTORY_SEPARATOR .$file;
    		if(isset($response['isError'])) {
    			if($response['isError'] !== true) {
    				//rename($ipFileName,$destFileName);   
    				
    				$fileNamePathIn = $trader_id. DIRECTORY_SEPARATOR .$partner_id. DIRECTORY_SEPARATOR . 'in'. DIRECTORY_SEPARATOR . $file;
    				$fileNamePathOut = $trader_id. DIRECTORY_SEPARATOR .$partner_id. DIRECTORY_SEPARATOR . 'out'. DIRECTORY_SEPARATOR . $file;    			
    				Storage::disk('public')->move($fileNamePathIn, $fileNamePathOut);    				
    			}
    		} else {
    			//rename($ipFileName,$destFileName);
    			$fileNamePathIn = $trader_id. DIRECTORY_SEPARATOR .$partner_id. DIRECTORY_SEPARATOR . 'in'. DIRECTORY_SEPARATOR . $file;
    			$fileNamePathOut = $trader_id. DIRECTORY_SEPARATOR .$partner_id. DIRECTORY_SEPARATOR . 'out'. DIRECTORY_SEPARATOR . $file;
    			
    			Storage::disk('public')->move($fileNamePathIn, $fileNamePathOut);
    
    		}
    	}
    }
}
