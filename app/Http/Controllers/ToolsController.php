<?php

namespace App\Http\Controllers;

/**
 * Free marketing tools for agencies (public, no auth). Each renders a small
 * interactive calculator / generator that agencies use daily, with SEO meta.
 */
class ToolsController extends Controller
{
    public function index()
    {
        return view('tools.index');
    }

    public function retainerCalculator()
    {
        return view('tools.retainer-calculator');
    }

    public function invoiceDueCalculator()
    {
        return view('tools.invoice-due-calculator');
    }

    public function proposalValueCalculator()
    {
        return view('tools.proposal-value-calculator');
    }
}
