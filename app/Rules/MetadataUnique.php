<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Metadata;

class MetadataUnique implements Rule
{
    private $database_id;
    private $metadata_id;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($database_id, $metadata_id = 'null')
    {
        $this->database_id = $database_id;
        $this->metadata_id = $metadata_id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $metadata = Metadata::where([
            ['table_name', $value],
            ['database_id', $this->database_id],
            ['id', '!=', $this->metadata_id]
        ])->first();

        if(is_null($metadata)){
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Ya existe metadata para la tabla seleccionada.';
    }
}
