<?php

namespace App\Enums;

enum ActivityType: string
{
    case Note = 'Note';
    case Call = 'Call';
    case Email = 'Email';
    case Meeting = 'Meeting';
    case Task = 'Task';
}
