<?php

namespace App\Enums;

enum DealStage: string
{
    case Lead = 'Lead';
    case Qualified = 'Qualified';
    case Proposal = 'Proposal';
    case Negotiation = 'Negotiation';
    case Won = 'Won';
    case Lost = 'Lost';
}
