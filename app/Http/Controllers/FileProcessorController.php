<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ReceiveMessageWFController;


use App\Http\Controllers\AppConfigController;
use Log;
use Storage;
use File;

class FileProcessorController extends Controller
{
    public $filePath;
    public function __construct() {

    }

    public function boot($trader,$partner) {
    	
    	if($trader == 0){
    		Log::warning("FileProcessorController boot : Invalid trader: " . $trader);
    	}
    	if($partner == 0){
    		Log::warning("FileProcessorController boot : Invalid partner: " . $partner);
    	}
    	$this->execute($trader,$partner);
    }
    
    public function execute($traderId =  0, $partnerId = 0) {
    	
    	$filePath = $traderId. DIRECTORY_SEPARATOR . $partnerId;
    	
    	if($dir_exist = $this->getDirContents()) {
    		
    		//$this->processPartnerInFiles("in", "processed", $this->filePath, $traderId, $partnerId);
    		$this->processPartnerInFiles("in", "processed", $traderId, $partnerId);
    	}
    	//echo $traderId.' - '.$partnerId;
    	
    }
    
    private function getDirContents() {
    	
    	$directories = array();
    	$directories = Storage::disk('public')->directories();
    	//$directories = Storage::disk('public/data')->directories();
    	//$directories = Storage::files('public/5/1/in');
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
    
    private function getDirFiles($path, $trader, $partner, $source) {
    	
    	//$path = public_path('uploads');
    	//$files = File::allfiles($path);
    	//$files = public_path('data/5/1/in');
    	//$files = Storage::files('public/data/5/1/in');
    	$path = $path. DIRECTORY_SEPARATOR .$trader. DIRECTORY_SEPARATOR .$partner. DIRECTORY_SEPARATOR .$source;
    	$files = Storage::files($path);
    	//$files = File::allfiles($path);
    	//$file = File::allfiles($path);
    	foreach($files as $file) {
    		$file = pathinfo($file);
    		$resp[] = $file['basename'];
    	}
    	
    	return $resp;
    }
    
    private function processPartnerInFiles($source, $dest, $trader, $partner) {
    	
    	$path = 'public'. DIRECTORY_SEPARATOR .'data'. DIRECTORY_SEPARATOR;
    	
    	$ipFiles = $this->getDirFiles($path, $trader, $partner, $source);
    	if(!$ipFiles){
    		$this->logger->LogError("empty folder: " . $filePath . "/" . $source );
    		return;
    	}
    
    	foreach($ipFiles as $file){
    		if(is_dir($file)) continue; //not an input file - processed/transmitted folders
    		//$workflow = new receiveMessageWF($this->logger);
    		$workflow = new ReceiveMessageWFController();
    		$response = $workflow->execute($trader, $partner, $file);
    		
    		$ipFileName = $filePath . "/" . $source . "/" . $file;
    		$destFileName =$filePath . "/" . $dest . "/" . $file;
    		$this->logger->LogDebug("calling workflow execute " . $ipFileName);
    		echo "processing: " . $ipFileName . "\n";
    		$response=$workflow->execute($trader, $partner, $ipFileName);
    		if(isset($response['isError'])){
    			if($response['isError']!==true){
    				rename($ipFileName,$destFileName);
    			}
    		}else{
    			rename($ipFileName,$destFileName);
    
    		}
    	}
    }
}
