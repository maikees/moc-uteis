<?php

namespace MOCUtils\Helpers;

/**
 * Class HelperController
 * @package MOCUtils\Helpers
 */
class HelperController
{
    private $object;

    /**
     * HelperController constructor.
     * @param \Closure $closure
     */
    public function __construct(\Closure $closure)
    {
        $erros = collect();

        $transaction = new Transaction($closure);

        if ($transaction->hasError()) {
            $message = $transaction->getError()->getMessage();

            $erros->push($message);
            new SlackException($message);
        }

        if ($erros->count()) {
            return redirect()->back()->withErrors($erros);
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
        return redirect()->back()->with($with);
    }

    public function getObject()
    {
        return $this->object;
    }
}
