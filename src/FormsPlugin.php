<?php

declare(strict_types=1);

namespace Magna\Forms;

use Magna\Admin\Nav\NavGroup;
use Magna\Admin\Nav\NavItem;
use Magna\Blocks\BlockDefinition;
use Magna\Blocks\Resolution\BlockDataResolver;
use Magna\Contracts\RegistersAdminNavigation;
use Magna\Contracts\RegistersBlocks;
use Magna\Contracts\RegistersSettingsPages;
use Magna\Forms\Blocks\FormBlockResolver;
use Magna\Forms\Blocks\FormOptions;
use Magna\Forms\Filament\Pages\FormsPage;
use Magna\Forms\Filament\Pages\SubmissionsPage;
use Magna\Plugins\Plugin;

/**
 * Magna Forms: build a form in the admin, drop it anywhere with the form
 * block, collect submissions.
 *
 * The plugin owns no rendering of its own beyond one block view — the
 * form appears wherever Pages puts it, styled by the active theme, which
 * is the whole point of contributing a block instead of a page.
 */
class FormsPlugin extends Plugin implements RegistersAdminNavigation, RegistersBlocks, RegistersSettingsPages
{
    public function boot(): void
    {
        $this->loadViewsFrom('resources/views', 'magna-forms');

        // Also on the shared magna:: namespace so the theme → magna block
        // view chain finds the form view.
        $this->loadViewsFrom('resources/views', 'magna');

        app(BlockDataResolver::class)->register(app(FormBlockResolver::class));
    }

    /** @return list<BlockDefinition> */
    public function blocks(): array
    {
        return [
            BlockDefinition::fromArray([
                'handle' => 'form',
                'label' => 'Form',
                'icon' => 'heroicon-o-envelope',
                'category' => 'site',
                'fields' => [
                    ['handle' => 'heading', 'type' => 'text', 'label' => 'Heading', 'required' => false],
                    [
                        'handle' => 'form',
                        'type' => 'select',
                        'label' => 'Form',
                        'required' => true,
                        'optionsFrom' => FormOptions::class,
                    ],
                ],
            ]),
        ];
    }

    public function adminNavigation(): NavGroup
    {
        return NavGroup::make('Forms', icon: 'heroicon-o-envelope')->items([
            NavItem::page('Forms', route: 'filament.admin.pages.forms-index')
                ->can('forms.manage'),
            NavItem::page('Submissions', route: 'filament.admin.pages.forms-submissions')
                ->can('forms.submissions'),
        ]);
    }

    /** @return list<class-string> */
    public function settingsPages(): array
    {
        return [FormsPage::class, SubmissionsPage::class];
    }
}
