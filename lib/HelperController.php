<?php

namespace MOCUtils\Helpers;

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
                return redirect()->back()->withErrors($this->error);
            }
        }

        if (!$this->error->count()) {
            if (request()->isJson()) {
                return response()->json($with);
            } else {
                return redirect()->back()->with($with);
            }
        }
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getErrors()
    {
        return $this->error;
    }
}
