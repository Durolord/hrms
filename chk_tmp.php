<?php
$u = App\Models\User::whereHas('roles', fn($q) => $q->where('name','Admin'))->first();
if (! $u) { echo "no admin user in local db\n"; return; }
echo (config('app.demo') ? 'DEMO' : 'NORMAL'), ' update=', var_export($u->can('update', $u), true), ' delete=', var_export($u->can('delete', $u), true), "\n";
