<?php

namespace TallStackUi\View\Components\Form;

use Illuminate\Contracts\View\View;
use TallStackUi\Foundation\Attributes\SoftPersonalization;
use TallStackUi\Foundation\Personalization\Contracts\Personalization;
use TallStackUi\View\Components\BaseComponent;

#[SoftPersonalization('form.error')]
class Error extends BaseComponent implements Personalization
{
    public function __construct(public ?string $wire = null)
    {
        //
    }

    public function blade(): View
    {
        return view('tallstack-ui::components.form.error');
    }

    public function personalization(): array
    {
        return ['text' => 'mt-2 text-sm font-medium text-red-500'];
    }
}
