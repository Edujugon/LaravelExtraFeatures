<?php

//IF unknown url, redirect to given url.
use Edujugon\LaravelExtraFeatures\ConfigData;

Route::any('{query}',
    function() { return redirect(ConfigData::getValue('REDIRECT_NO_PAGE_FOUND')); })
    ->where('query', '.*');