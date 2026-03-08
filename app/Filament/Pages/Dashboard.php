<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Dashboard extends BaseDashboard
{
    public function getHeading(): string
    {
        $name = Auth::user()?->name ?? 'Admin';
        return "Welcome, {$name}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sign_out')
                ->label('Sign Out')
                ->icon('heroicon-o-arrow-right-on-rectangle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Sign Out')
                ->modalDescription('Are you sure you want to sign out?')
                ->modalSubmitActionLabel('Yes, sign out')
                ->action(function () {
                    Auth::logout();
                    Session::invalidate();
                    Session::regenerateToken();
                    return redirect(filament()->getLoginUrl());
                }),
        ];
    }
}
