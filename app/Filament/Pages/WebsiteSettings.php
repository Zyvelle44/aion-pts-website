<?php

namespace App\Filament\Pages;

use App\Models\WebsiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;

class WebsiteSettings extends Page
{
    /**
     * Website-wide settings can only be changed by a Super Admin.
     * Hiding the navigation item alone is not sufficient authorization.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Website Settings';
    protected static ?string $title = 'Website Settings';
    protected static ?string $slug = 'website-settings';
    protected static string | \UnitEnum | null $navigationGroup = 'Website Management';
    protected string $view = 'filament.pages.website-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $keys = ['site_name','tagline','meta_description','logo_path','favicon_path','server_name','server_version','server_rate','level_cap','contact_email','discord_url','facebook_url','maintenance_mode','maintenance_message'];
        $state = [];
        foreach ($keys as $key) {
            $state[$key] = WebsiteSetting::get($key, in_array($key, ['maintenance_mode'], true) ? false : '');
        }
        $state['maintenance_mode'] = filter_var($state['maintenance_mode'], FILTER_VALIDATE_BOOLEAN);
        $this->form->fill($state);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('General')->description('Public identity and SEO details.')->columns(2)->schema([
                TextInput::make('site_name')->label('Website Name')->required()->maxLength(100),
                TextInput::make('tagline')->label('Tagline')->maxLength(180),
                FileUpload::make('logo_path')->label('Website Logo')->image()->disk('public')->directory('site-assets')->visibility('public')->maxSize(2048)->helperText('PNG, JPG or WebP. Recommended: transparent PNG, up to 2 MB.'),
                FileUpload::make('favicon_path')->label('Favicon Image')->image()->disk('public')->directory('site-assets')->visibility('public')->maxSize(1024)->helperText('PNG, JPG or WebP. Use a square image, up to 1 MB.'),
                Textarea::make('meta_description')->label('Meta Description')->rows(3)->columnSpanFull()->maxLength(320),
            ]),
            Section::make('Game Server')->columns(2)->schema([
                TextInput::make('server_name')->label('Server Name')->maxLength(100),
                TextInput::make('server_version')->label('Game Version')->maxLength(40),
                TextInput::make('server_rate')->label('Server Rates')->maxLength(80),
                TextInput::make('level_cap')->label('Level Cap')->numeric()->minValue(1)->maxValue(999),
            ]),
            Section::make('Contact & Community')->columns(2)->schema([
                TextInput::make('contact_email')->label('Support Email')->email()->maxLength(190),
                TextInput::make('discord_url')->label('Discord Invite URL')->url()->maxLength(255),
                TextInput::make('facebook_url')->label('Facebook URL')->url()->maxLength(255),
            ]),
            Section::make('Maintenance Mode')->schema([
                Checkbox::make('maintenance_mode')->label('Enable maintenance notice on the public website'),
                Textarea::make('maintenance_message')->label('Maintenance Message')->rows(3)->maxLength(500),
            ]),
        ])->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [Action::make('save')->label('Save Settings')->icon('heroicon-o-check')->action('save')];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $allowed = ['site_name','tagline','meta_description','logo_path','favicon_path','server_name','server_version','server_rate','level_cap','contact_email','discord_url','facebook_url','maintenance_mode','maintenance_message'];
        WebsiteSetting::putMany(Arr::only($data, $allowed));
        Notification::make()->title('Website settings saved')->success()->send();
    }
}
