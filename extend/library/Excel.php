<?php

namespace library;

use PHPExcel_IOFactory;
use PHPExcel_Cell;
use PHPExcel;

class Excel
{
    public function __construct(){}

    /**
     * 读Excel数据
     *  
     * @param  string $path    路径
     * @param  string $type    表类型(xls|xlsx)
     */
    public function read($path, $type){
        if(strtolower ($type )=='xls')
        {
            $objReader = PHPExcel_IOFactory::createReader('Excel5');

        }
        elseif(strtolower ($type)=='xlsx')
        {
            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        }

        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($path);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
        
        $excelData = array();
        
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $excelData[$row][] =(string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            }
        }

        return $excelData;
    }
}