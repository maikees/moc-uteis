<?php

namespace MOCUtils\Helpers;

use Illuminate\Support\Facades\Redirect;

/**
 * Class HelperController
 * @package MOCUtils\Helpers
 */
class HelperController
{
    /**
     * @var array
     */
    private $object;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $error;

    /**
     * @var Redirect
     */
    private $redirect;

    /**
     * HelperController constructor.
     * @param \Closure $closure
     */
    public function __construct(\Closure $closure)
    {
        $this->error = collect();

        $transaction = new Transaction($closure);

        if ($transaction->hasError()) {
            $message = $transaction->getError()->getMessage();

            $this->error->push($message);
            new SlackException($message);
        }

        $this->object = $transaction->getResults();
        $this->redirect = redirect()->back();

        return $this;
    }

    /**
     * @param $with
     * @return \Illuminate\Http\RedirectResponse
     */
    public function BackToWith($with)
    {
        if ($this->error->count()) {
            if (request()->isJson()) {
                return response()->json($this->error);
            } else {
                return $this->redirect->withErrors($this->error);
            }
        }

        if (!$this->error->count()) {
            if (request()->isJson()) {
                return response()->json($with);
            } else {
                return $this->redirect->with($with);
            }
        }
    }

    /**
     * @return array
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getErrors()
    {
        return $this->error;
    }

    /**
     * @param $redirect
     */
    public function setRedirect($redirect)
    {
        $this->redirect = $redirect;
    }
}
