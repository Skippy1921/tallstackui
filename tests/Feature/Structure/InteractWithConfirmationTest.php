<?php

use TallStackUi\Interactions\Dialog;
use TallStackUi\Interactions\Toast;
use TallStackUi\Interactions\Traits\InteractWithConfirmation;

test('can only be used in Dialog and Toast')
    ->expect(InteractWithConfirmation::class)
    ->toOnlyBeUsedIn([
        Dialog::class,
        Toast::class,
    ]);
