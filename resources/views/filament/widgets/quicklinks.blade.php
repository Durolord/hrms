<x-filament-widgets::widget>
    <x-filament::section>
        <div>
    <!-- Attendance Actions Group -->
    <x-filament-actions::group
        :actions="[
            $this->overview(),
            $this->attendances(),
            $this->createAttendance(),
        ]"
        label="Attendance Actions"
        icon="heroicon-m-clock"
        color="primary"
        size="md"
        tooltip="Manage attendances"
        dropdown-placement="bottom-start"
    />
    <!-- Applicant Actions Group -->
    <x-filament-actions::group
        :actions="[
            $this->applicants(),
        ]"
        label="Applicant Actions"
        icon="heroicon-m-user-group"
        color="secondary"
        size="md"
        tooltip="Manage applicants"
        dropdown-placement="bottom-start"
    />
    <!-- Allowance Actions Group -->
    <x-filament-actions::group
        :actions="[
            $this->allowances(),
            $this->createAllowance(),
        ]"
        label="Allowance Actions"
        icon="heroicon-m-currency-dollar"
        color="primary"
        size="md"
        tooltip="Manage allowances"
        dropdown-placement="bottom-start"
    />
    <!-- Payroll Actions Group -->
    <x-filament-actions::group
        :actions="[
            $this->payrolls(),
            $this->createPayroll(),
        ]"
        label="Payroll Actions"
        icon="heroicon-m-document-text"
        color="primary"
        size="md"
        tooltip="Manage payrolls"
        dropdown-placement="bottom-start"
    />
    <!-- Employee Actions Group -->
    <x-filament-actions::group
        :actions="[
            $this->employees(),
            $this->createEmployee(),
        ]"
        label="Employee Actions"
        icon="heroicon-m-user"
        color="primary"
        size="md"
        tooltip="Manage employees"
        dropdown-placement="bottom-start"
    />
    <!-- Deduction Actions Group -->
    <x-filament-actions::group
        :actions="[
            $this->deductions(),
            $this->createDeduction(),
        ]"
        label="Deduction Actions"
        icon="heroicon-m-minus-circle"
        color="primary"
        size="md"
        tooltip="Manage deductions"
        dropdown-placement="bottom-start"
    />
    <!-- Bonus Actions Group -->
    <x-filament-actions::group
        :actions="[
            $this->bonuses(),
            $this->createBonus(),
        ]"
        label="Bonus Actions"
        icon="heroicon-m-gift"
        color="primary"
        size="md"
        tooltip="Manage bonuses"
        dropdown-placement="bottom-start"
    />
    <!-- Branch Actions Group -->
    <x-filament-actions::group
        :actions="[
            $this->branches(),
            $this->createBranch(),
        ]"
        label="Branch Actions"
        icon="heroicon-m-map"
        color="primary"
        size="md"
        tooltip="Manage branches"
        dropdown-placement="bottom-start"
    />
    <!-- Leave Actions Group -->
    <x-filament-actions::group
        :actions="[
            $this->leaveTypes(),
            $this->leaves(),
            $this->createLeaveType(),
            $this->createLeave(),
        ]"
        label="Leave Actions"
        icon="heroicon-m-calendar"
        color="primary"
        size="md"
        tooltip="Manage leaves"
        dropdown-placement="bottom-start"
    />
    <!-- Opening Actions Group -->
    <x-filament-actions::group
        :actions="[
            $this->openings(),
            $this->createOpening(),
        ]"
        label="Opening Actions"
        icon="heroicon-m-building-office"
        color="primary"
        size="md"
        tooltip="Manage openings"
        dropdown-placement="bottom-start"
    />
    <!-- Pay Scale Actions Group -->
    <x-filament-actions::group
        :actions="[
            $this->payScales(),
            $this->createPayScale(),
        ]"
        label="Pay Scale Actions"
        icon="heroicon-m-scale"
        color="primary"
        size="md"
        tooltip="Manage pay scales"
        dropdown-placement="bottom-start"
    />
    <!-- Role Actions Group -->
    <x-filament-actions::group
        :actions="[
            $this->roles(),
            $this->createRole(),
        ]"
        label="Role Actions"
        icon="heroicon-m-shield-check"
        color="primary"
        size="md"
        tooltip="Manage roles"
        dropdown-placement="bottom-start"
    />
    <!-- Skill Actions Group -->
    <x-filament-actions::group
        :actions="[
            $this->skills(),
            $this->createSkill(),
        ]"
        label="Skill Actions"
        icon="heroicon-m-bolt"
        color="primary"
        size="md"
        tooltip="Manage skills"
        dropdown-placement="bottom-start"
    />
    <x-filament-actions::modals />
</div>
    </x-filament::section>
</x-filament-widgets::widget>