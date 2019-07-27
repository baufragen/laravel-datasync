<?php

namespace Baufragen\DataSync\Rules;

use Illuminate\Contracts\Validation\Rule;

class CorrectDataSyncApiKey implements Rule
{
    protected $connection;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
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
        return $value == config('datasync.connections.' . $this->connection . '.apikey');
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid API key for data sync.';
    }
}
