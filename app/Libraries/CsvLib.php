<?php
namespace App\Libraries;
use Illuminate\Http\Request;
use Excel;
/**
 * CsvLib Class
 *
 * @package                Safe Health
 * @subpackage             CsvLib
 * @category               Library
 * @DateOfCreation         05 Apr 2018
 * @ShortDescription       This class is responsible for import and export in CSV and EXCEL.

 */
class CsvLib {
  
    /**
     * Create a new library instance.
     *
     * @return void
     */
    public function __construct()
    {
  
    }
  
    /**
    * @DateOfCreation        05 Apr 2018
    * @ShortDescription      This function is responsible to import data from file .
    * @param                 String $fileToImport
    * @return                Array  ( All imported data ) 
    */
    public function importData($fileToImport)
    {   
        if(!empty($fileToImport)){
            $path = $fileToImport->getRealPath();
            $data = Excel::load($path)->toArray();
            if(count($data)){
                if(!empty($data)){
                    return ['code' => '1000','message' => __('messages.1001'),'result' => $data];
                }else{
                    return ['code' => '5000', 'message' => __('messages.5001'), 'result' => []];
                }
            }
        }else{
            return ['code' => '5000', 'message' => __('messages.5004'), 'result' => []];
        }
    }


   /**
    * @DateOfCreation        05 Apr 2018
    * @DateOfDeprecated      12 Apr 2018
    * @ShortDescription      This function is export data in given format and download file.
    * @LoongDescription      Extra info parameter is optional if it is empty or the value of 
                             sheet title and name is empty we will assign the downloadFileName 
                             parameter to both places. Columns and Header must be same in  length.   
    * @param                 Array $data   - Format $data = [
                                                                ['C1','C2','C3'],
                                                                ['C11','C12','C13'],
                                                                ['C21','C22','C23']
                                                            ]                          
                             Array $headers 
                             String $downloadFileName  - 'Format supported CSV ,XLS, XLSX'
                             String $downloadType
                             Array $extraInfo (
                                String sheetTitle
                                String sheetName
                             )  (This parameter is optional) 
    * @return                Download file
    */
    public function exportData($data,$headers,$downloadFileName,$downloadType,$extraInfo = [])
    {
        if(!empty($extraInfo)){
            $sheetTitle = (!empty($extraInfo['sheetTitle']) ? $extraInfo['sheetTitle'] : $downloadFileName);
            $sheetName  = (!empty($extraInfo['sheetName']) ? $extraInfo['sheetName'] : $downloadFileName);
        }else{
            $sheetTitle = $downloadFileName;
            $sheetName = $downloadFileName;
        }
        $dataLength = count($headers);

        if(empty($data)){
            return ['code' => '5000','message' => __('messages.5015')];
        }
        if(empty($headers)){
            return ['code' => '5000','message' => __('messages.5016')];
        }
        if(empty($downloadFileName)){
            return ['code' => '5000','message' => __('messages.5017')];
        }
        if(empty($downloadType)){
            return ['code' => '5000','message' => __('messages.5018')];
        }
        
        $verifyData = $this->checkData($data,$dataLength);


        if($verifyData){
            return Excel::create($downloadFileName, function($excel) use ($data,$headers,$sheetTitle,$sheetName,$downloadType,$dataLength) {
                $excel->setTitle($sheetTitle);
                $excel->sheet($sheetName, function($sheet) use ($data,$headers,$downloadType,$dataLength)
                {
                    $i = 'A';
                    $j = 1;
                    foreach ($headers as $value) {
                        $sheet->cell($i.$j, function($cell) use ($value,$dataLength){
                            $cell->setValue($value);
                        });
                        $i++;
                    }
                    if (!empty($data)) {
                        foreach ($data as $key => $value) {
                            $k= $key+2;$column = 'A';
                            for ($i=0; $i < $dataLength; $i++) { 
                                $sheet->cell($column.$k, $value[$i]);
                                $column++;
                            }
                        }
                    }
                })->download($downloadType);
            });
        }else{
            return ['code' => '5000','message' => __('messages.5019')];
        }      
    }
}