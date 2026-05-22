<?php

namespace App\Enums;

enum SyncStatus: string
{
    case SUCCESS = 'success';
    case FAILED = 'failed';
}
