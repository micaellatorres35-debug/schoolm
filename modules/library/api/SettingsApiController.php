<?php
class SettingsApiController
{
    private Settings    $settings;
    private ActivityLog $log;

    public function __construct()
    {
        $this->settings = new Settings();
        $this->log      = new ActivityLog();
    }

    public function handle(string $action, array $input): never
    {
        match ($action) {
            'get_settings'  => $this->getSettings(),
            'save_settings' => $this->saveSettings($input),
            default         => Response::error("Unknown settings action: {$action}"),
        };
    }

    private function getSettings(): never
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        Response::json($this->settings->all());
    }

    private function saveSettings(array $input): never
    {
        $this->settings->save($input);
        $this->log->log('Settings Updated', 'Library settings updated');

        Response::json([
            'success'  => true,
            'message'  => 'Settings saved!',
            'settings' => $this->settings->all(),
        ]);
    }
}