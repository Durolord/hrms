<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Opening;
use App\Models\Qualification;
use App\Models\Responsibility;
use Illuminate\Database\Seeder;

class SalesDevelopmentOpeningSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            'Human Resources',
            'Finance',
            'IT',
            'Sales',
            'Operations',
        ];
        $branches = [
            'Lagos Branch',
            'Abuja Branch',
        ];
        foreach ($departments as $departmentName) {
            $department = Department::where('name', $departmentName)->first();
            foreach ($branches as $branchName) {
                $branch = Branch::where('name', $branchName)->first();
                $jobTitle = $this->getJobTitle($departmentName);
                $qualifications = $this->getQualifications($departmentName);
                $responsibilities = $this->getResponsibilities($departmentName);
                $opening = Opening::create([
                    'title' => $jobTitle,
                    'description' => $this->getDescription($departmentName),
                    'department_id' => $department->id,
                    'designation_id' => $this->getDesignationId($departmentName),
                    'branch_id' => $branch->id,
                    'active' => true,
                ]);
                foreach ($qualifications as $qualificationText) {
                    $qualification = Qualification::firstOrCreate(['description' => $qualificationText]);
                    $opening->qualifications()->attach($qualification);
                }
                foreach ($responsibilities as $responsibilityText) {
                    $responsibility = Responsibility::firstOrCreate(['description' => $responsibilityText]);
                    $opening->responsibilities()->attach($responsibility);
                }
            }
        }
    }

    private function getJobTitle($departmentName)
    {
        $titles = [
            'Human Resources' => 'HR Manager',
            'Finance' => 'Finance Analyst',
            'IT' => 'Software Engineer',
            'Sales' => 'Sales Development Representative',
            'Operations' => 'Operations Manager',
        ];

        return $titles[$departmentName] ?? 'General Manager';
    }

    private function getQualifications($departmentName)
    {
        $qualifications = [
            'Human Resources' => [
                'Bachelor\'s degree in Human Resources or related field',
                '3+ years of experience in HR management',
                'Strong knowledge of HR practices and employment law',
            ],
            'Finance' => [
                'Bachelor\'s degree in Finance, Accounting, or related field',
                'Experience with financial analysis and reporting',
                'Strong knowledge of accounting principles',
            ],
            'IT' => [
                'Bachelor\'s degree in Computer Science or related field',
                'Proficiency in programming languages such as Java or Python',
                'Experience with system administration and network security',
            ],
            'Sales' => [
                'Bachelor\'s degree in Business, Marketing or related field',
                '1+ years experience in SaaS sales',
                'Proven track record of meeting sales quotas',
            ],
            'Operations' => [
                'Bachelor\'s degree in Business Administration or related field',
                '3+ years of experience in operations management',
                'Strong leadership and problem-solving skills',
            ],
        ];

        return $qualifications[$departmentName] ?? [];
    }

    private function getResponsibilities($departmentName)
    {
        $responsibilities = [
            'Human Resources' => [
                'Recruit, interview, and hire new employees',
                'Ensure compliance with labor laws and regulations',
                'Develop and implement HR policies and procedures',
            ],
            'Finance' => [
                'Prepare financial statements and reports',
                'Monitor and analyze financial performance',
                'Manage budgets and financial forecasts',
            ],
            'IT' => [
                'Develop and maintain software applications',
                'Ensure system security and data protection',
                'Provide technical support to staff',
            ],
            'Sales' => [
                'Generate business through proactive outreach via cold calling and email campaigns',
                'Create and deliver product presentations',
                'Manage/close high-velocity inbound sales opportunities',
            ],
            'Operations' => [
                'Oversee daily operations and ensure efficiency',
                'Develop and implement operational strategies',
                'Manage supply chain and vendor relationships',
            ],
        ];

        return $responsibilities[$departmentName] ?? [];
    }

    private function getDesignationId($departmentName)
    {
        $designations = [
            'Human Resources' => 'HR Manager',
            'Finance' => 'Finance Analyst',
            'IT' => 'Software Engineer',
            'Sales' => 'Sales Development Representative',
            'Operations' => 'Operations Manager',
        ];

        return Designation::firstOrCreate([
            'name' => $designations[$departmentName],
            'pay_scale_id' => 3,
            'status' => true,
        ])->id;
    }

    private function getDescription($departmentName)
    {
        $descriptions = [
            'Human Resources' => 'The HR Manager will oversee recruitment, training, employee relations, and ensure compliance with labor laws.',
            'Finance' => 'The Finance Analyst will manage financial reports, budgets, and conduct analysis to support decision-making.',
            'IT' => 'The Software Engineer will develop and maintain our software solutions while ensuring the security and performance of IT systems.',
            'Sales' => 'The Sales Development Representative will focus on generating business and building relationships with clients.',
            'Operations' => 'The Operations Manager will manage day-to-day operations, ensuring processes run smoothly and efficiently.',
        ];

        return $descriptions[$departmentName] ?? 'General job description.';
    }
}
