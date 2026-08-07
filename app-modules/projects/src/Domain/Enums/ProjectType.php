<?php

namespace Modules\Projects\Domain\Enums;

enum ProjectType: string
{
    case WebsiteDesign = 'website_design';
    case Seo = 'seo';
    case DigitalMarketing = 'digital_marketing';
    case Support = 'support';
    case Other = 'other';
}
