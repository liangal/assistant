<?php

Route::group('/manage', function () {
    Route::post('/autopreview', 'AutoPreview@executecommand');
})->prefix('\app\http\controllers\manage\\');
