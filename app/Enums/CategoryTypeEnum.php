<?php

namespace App\Enums;

enum CategoryTypeEnum: string
{
    case INCOME = 'income';
    case EXPENSE = 'expense';
    case INVESTMENT = 'investment';
}
