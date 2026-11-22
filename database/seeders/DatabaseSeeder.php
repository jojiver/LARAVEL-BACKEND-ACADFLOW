<?php

namespace Database\Seeders;

use App\Models\ClassSchedule;
use App\Models\Subject;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! filter_var(env('SEED_DEMO', true), FILTER_VALIDATE_BOOL)) {
            return;
        }

        $user = User::query()->updateOrCreate(
            ['email' => 'alex@acadflow.test'],
            [
                'name' => 'Alex Morgan',
                'password' => Hash::make('Password123'),
                'student_id' => null,
                'program' => 'BSIT',
                'year_level' => '4th Year',
                'academic_term' => '1st Semester',
                'role' => 'STUDENT',
            ],
        );

        $subjectData = [
            ['name' => 'Web Development', 'instructor' => 'Prof. Reyes', 'room' => 'Lab 1', 'schedule' => 'Mon 8–10', 'category' => 'Core'],
            ['name' => 'Database Mgmt', 'instructor' => 'Prof. Tan', 'room' => 'Rm 204', 'schedule' => 'Tue 10–12', 'category' => 'Core'],
            ['name' => 'Networking', 'instructor' => 'Prof. Cruz', 'room' => 'Lab 2', 'schedule' => 'Wed 1–3', 'category' => 'Elective'],
            ['name' => 'Programming', 'instructor' => 'Prof. Lim', 'room' => 'Rm 108', 'schedule' => 'Thu 2–4', 'category' => 'Elective'],
            ['name' => 'Human Computer Interaction', 'instructor' => 'Prof. Santos', 'room' => 'Lab 3', 'schedule' => 'Fri 1–3', 'category' => 'Core'],
            ['name' => 'Data Structures', 'instructor' => 'Prof. Gomez', 'room' => 'Rm 112', 'schedule' => 'Fri 9–11', 'category' => 'Archived'],
        ];

        $subjects = [];
        foreach ($subjectData as $data) {
            $subjects[$data['name']] = Subject::query()->updateOrCreate(
                ['user_id' => $user->id, 'name' => $data['name']],
                [...$data, 'user_id' => $user->id],
            );
        }

        $scheduleData = [
            ['Web Development', 'Mon', '08:00', '10:00', 'Lab 1'],
            ['Programming', 'Mon', '13:00', '14:30', 'Room 108'],
            ['Database Mgmt', 'Tue', '10:00', '12:00', 'Room 204'],
            ['Human Computer Interaction', 'Tue', '15:00', '16:00', 'Room 305'],
            ['Web Development', 'Wed', '08:00', '10:00', 'Lab 1'],
            ['Database Mgmt', 'Wed', '13:00', '15:00', 'Room 204'],
            ['Networking', 'Wed', '16:00', '17:00', null],
            ['Networking', 'Thu', '09:00', '11:00', 'Lab 2'],
            ['Programming', 'Thu', '14:00', '16:00', 'Room 108'],
            ['Database Mgmt', 'Fri', '08:00', '10:00', 'Room 204'],
        ];

        foreach ($scheduleData as [$subjectName, $day, $start, $end, $room]) {
            ClassSchedule::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'subject_id' => $subjects[$subjectName]->id,
                    'day' => $day,
                    'start_time' => $start,
                ],
                [
                    'end_time' => $end,
                    'room' => $room,
                ],
            );
        }

        $taskData = [
            [
                'title' => 'Web Development Final Project',
                'subject' => 'Web Development',
                'type' => 'Project',
                'description' => 'Create a Laravel REST API.',
                'deadline' => now()->addDays(2)->setTime(23, 59),
                'hours' => 8,
                'difficulty' => 'Hard',
                'importance' => 'High',
                'progress' => 33,
                'status' => 'In progress',
                'steps' => [['Set up the project', true], ['Build the API endpoints', false], ['Write documentation', false]],
            ],
            [
                'title' => 'Database Assignment',
                'subject' => 'Database Mgmt',
                'type' => 'Assignment',
                'description' => 'Normalize the tables to 3NF and explain each step.',
                'deadline' => now()->addDays(5)->setTime(23, 59),
                'hours' => 3,
                'difficulty' => 'Medium',
                'importance' => 'High',
                'progress' => 33,
                'status' => 'In progress',
                'steps' => [['Draw the ER diagram', true], ['Normalize to 3NF', false], ['Write the explanation', false]],
            ],
            [
                'title' => 'Reflection Paper',
                'subject' => 'Human Computer Interaction',
                'type' => 'Research Paper',
                'description' => 'Reflect on the usability evaluation activity.',
                'deadline' => now()->addDays(9)->setTime(23, 59),
                'hours' => 1,
                'difficulty' => 'Easy',
                'importance' => 'Low',
                'progress' => 70,
                'status' => 'In progress',
                'steps' => null,
            ],
            [
                'title' => 'Networking Lab Report',
                'subject' => 'Networking',
                'type' => 'Laboratory',
                'description' => 'Analyze the captured network traffic.',
                'deadline' => now()->subDay()->setTime(23, 59),
                'hours' => 4,
                'difficulty' => 'Medium',
                'importance' => 'High',
                'progress' => 33,
                'status' => 'In progress',
                'steps' => [['Collect packet captures', true], ['Analyze traffic', false], ['Write the conclusion', false]],
            ],
            [
                'title' => 'HCI Prototype Presentation',
                'subject' => 'Human Computer Interaction',
                'type' => 'Presentation',
                'description' => 'Present the low-fidelity prototype.',
                'deadline' => now()->addDay()->setTime(23, 59),
                'hours' => 5,
                'difficulty' => 'Hard',
                'importance' => 'Medium',
                'progress' => 67,
                'status' => 'In progress',
                'steps' => [['Build the prototype', true], ['Test the flow', true], ['Prepare the slides', false]],
            ],
            [
                'title' => 'Programming Quiz Reviewer',
                'subject' => 'Programming',
                'type' => 'Quiz',
                'description' => 'Review recursion and data structures.',
                'deadline' => now()->addDays(3)->setTime(23, 59),
                'hours' => 2,
                'difficulty' => 'Medium',
                'importance' => 'Medium',
                'progress' => 10,
                'status' => 'In progress',
                'steps' => null,
            ],
            [
                'title' => 'Thesis Capstone Requirement',
                'subject' => null,
                'type' => 'Thesis/Capstone',
                'description' => 'Finalize chapter 1 and prepare the defense.',
                'deadline' => now()->addDays(7)->setTime(23, 59),
                'hours' => 6,
                'difficulty' => 'Hard',
                'importance' => 'High',
                'progress' => 25,
                'status' => 'In progress',
                'steps' => [['Finalize chapter 1', true], ['Gather literature', false], ['Build prototype', false], ['Prepare slides', false]],
            ],
            [
                'title' => 'Database ERD Activity',
                'subject' => 'Database Mgmt',
                'type' => 'Activity',
                'description' => 'Completed ERD exercise.',
                'deadline' => now()->subDays(2)->setTime(23, 59),
                'hours' => 2,
                'difficulty' => 'Medium',
                'importance' => 'Medium',
                'progress' => 100,
                'status' => 'Completed',
                'completed_at' => now()->subHours(3),
                'steps' => null,
            ],
            [
                'title' => 'Networking Quiz',
                'subject' => 'Networking',
                'type' => 'Quiz',
                'description' => 'Completed networking quiz.',
                'deadline' => now()->subDays(4)->setTime(23, 59),
                'hours' => 1,
                'difficulty' => 'Easy',
                'importance' => 'Medium',
                'progress' => 100,
                'status' => 'Completed',
                'completed_at' => now()->subDay(),
                'steps' => null,
            ],
            [
                'title' => 'Web Development Wireframe',
                'subject' => 'Web Development',
                'type' => 'Activity',
                'description' => 'Completed wireframe activity.',
                'deadline' => now()->subDays(6)->setTime(23, 59),
                'hours' => 3,
                'difficulty' => 'Medium',
                'importance' => 'Low',
                'progress' => 100,
                'status' => 'Completed',
                'completed_at' => now()->subDays(3),
                'steps' => null,
            ],
        ];

        foreach ($taskData as $data) {
            $steps = $data['steps'] ?? null;
            $subjectId = $data['subject'] ? $subjects[$data['subject']]->id : null;

            $task = Task::query()->updateOrCreate(
                ['user_id' => $user->id, 'title' => $data['title']],
                [
                    'user_id' => $user->id,
                    'subject_id' => $subjectId,
                    'title' => $data['title'],
                    'type' => $data['type'],
                    'description' => $data['description'],
                    'deadline' => $data['deadline'],
                    'estimated_hours' => $data['hours'],
                    'difficulty' => $data['difficulty'],
                    'importance' => $data['importance'],
                    'progress' => $data['progress'],
                    'status' => $data['status'],
                    'completed_at' => $data['completed_at'] ?? null,
                ],
            );

            $task->steps()->delete();
            foreach ($steps ?? [] as $index => [$label, $done]) {
                $task->steps()->create([
                    'label' => $label,
                    'done' => $done,
                    'position' => $index,
                ]);
            }
        }
    }
}
