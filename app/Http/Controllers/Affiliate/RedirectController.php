<?php

namespace App\Http\Controllers\Affiliate;

use App\Actions\Affiliate\RecordAffiliateClickAndRedirectAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class RedirectController extends Controller
{
    protected RecordAffiliateClickAndRedirectAction $redirectAction;

    public function __construct(RecordAffiliateClickAndRedirectAction $redirectAction)
    {
        $this->redirectAction = $redirectAction;
    }

    /**
     * Handle the affiliate redirect POST action.
     * Commits click to DB before redirecting to Shopee.
     *
     * @param int $product
     * @return RedirectResponse
     */
    public function __invoke($product): RedirectResponse
    {
        return $this->redirectAction->execute($product);
    }
}
