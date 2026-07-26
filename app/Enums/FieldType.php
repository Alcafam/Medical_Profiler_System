<?php

namespace App\Enums;

enum FieldType: string
{
    case Text = 'text';
    case Number = 'number';
    case Date = 'date';
    case Select = 'select';
    case Textarea = 'textarea';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Text',
            self::Number => 'Number',
            self::Date => 'Date',
            self::Select => 'Select',
            self::Textarea => 'Textarea',
        };
    }
}
