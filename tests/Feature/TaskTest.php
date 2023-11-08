<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private $mockedUsers = [];
    private $mockedTasks = [];

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create();
        User::factory()->create();

        $user1 = User::first();

        $user2 = User::where('id', '!=', $user1->id)->first();

        array_push($this->mockedUsers, $user1, $user2);

        $this->actingAs($user1);

        $tasks = [
            [
                'name'    => 'Task 1',
                'status'  => Task::STATUS_NOT_STARTED,
                'user_id' => $user1->id,
            ],
            [
                'name'    => 'Task 2',
                'status'  => Task::STATUS_IN_PROGRESS,
                'user_id' => $user1->id,
            ],
            [
                'name' => 'Task 3',
                'status'  => Task::STATUS_COMPLETED,
                'user_id' => $user1->id,
            ],
            [
                'name'    => 'Task 4',
                'status'  => Task::STATUS_COMPLETED,
                'user_id' => $user2->id,
            ],
        ];

        Task::insert($tasks);

        $this->mockedTasks = Task::with('user', 'files')
            ->get()
            ->toArray();
    }

    public function test_redirect_not_logged_in_user(): void
    {
        Auth::logout();

        $response = $this->get(route('home'));
        $response->assertStatus(302);
    }

    public function test_home(): void
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);

        $response->assertViewIs('home');
        $response->assertViewHas('completed_count');
        $response->assertViewHas('uncompleted_count');

        $completed_count = $response->viewData('completed_count');
        $uncompleted_count = $response->viewData('uncompleted_count');

        $this->assertEquals(2, $completed_count);
        $this->assertEquals(2, $uncompleted_count);
    }

    public function test_index_without_permission(): void
    {
        $response = $this->get(route('tasks.index'));

        $response->assertStatus(200);
        $response->assertViewIs('tasks.index');

        $tasks = $response->viewData('tasks')->toArray();

        $expectedTasks = [
            $this->mockedTasks[0],
            $this->mockedTasks[1],
            $this->mockedTasks[2],
        ];

        $this->assertEquals($expectedTasks, $tasks);
    }

    public function test_index_with_right_permission(): void
    {
        Gate::shouldReceive('allows')
            ->with('viewAnyTask', Task::class)
            ->andReturn(true);
        Gate::shouldReceive('allows')
            ->with('updateAnyTask', Task::class)
            ->andReturn(true);
        Gate::shouldReceive('allows')
            ->with('deleteAnyTask', Task::class)
            ->andReturn(true);
        Gate::shouldReceive('any')->andReturn(false);
        Gate::shouldReceive('check')->andReturn(false);

        $response = $this->get(route('tasks.index'));
        $response->assertStatus(200);

        $tasks = $response->viewData('tasks');

        $expectedTasks = $this->mockedTasks;

        $this->assertEquals($expectedTasks, $tasks->toArray());
    }

    public function test_create()
    {
        $response = $this->get(route('tasks.create'));

        $response->assertStatus(200);
        $response->assertViewIs('tasks.create');
        $response->assertViewHas('pageTitle');

        $pageTitle = $response->viewData('pageTitle');

        $this->assertEquals('Create Task', $pageTitle);
    }

    public function test_store_without_file()
    {
        $newTask = [
            'name'     => 'New Task',
            'detail'   => 'New Task Detail',
            'due_date' => date('Y-m-d', time()),
            'status'   => Task::STATUS_IN_PROGRESS,
        ];
    
        // Request berjenis post ke method tasks.store
        $response = $this->post(route('tasks.store'), $newTask);
    
        $response->assertStatus(302);
        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHasNoErrors();
    
        // Memeriksa apakah tabel "tasks" memiliki data yang sama dengan $newTask 
        $this->assertDatabaseHas('tasks', $newTask);
    }

    public function test_store_with_file()
    {
        Storage::fake('public');

        $newTask = [
            'name' => 'New Task',
            'detail' => 'New Task detail',
            'due_date' => date('Y-m-d', time()),
            'status' => Task::STATUS_IN_PROGRESS,
        ];

        $file = UploadedFile::fake()->image('test_image.png');

        $response = $this->post(
            route('tasks.store'),
            array_merge($newTask, ['file' => $file])
        );

        $response->assertStatus(302);
        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tasks', $newTask);

        $task = Task::where('name', 'New Task')->first();
        $this->assertNotNull($task->files);

        $filePath = $task->files[0]->path;
        Storage::disk('public')->assertExists($filePath);
    }

    public function test_store_invalid_request()
    {
        $response = $this->post(route('tasks.store'), [
            'detail' => 'New Task',
        ]);

        $response->assertSessionHasErrors(['name', 'due_date', 'status']);
    }

    public function test_edit_with_right_permission()
    {
        Gate::shouldReceive('denies')
            ->with('performAsTaskOwner', Task::class)
            ->andReturn(true);

        Gate::shouldReceive('authorize')
            ->with('updateAnyTask', Task::class)
            ->andReturn(true);

        Gate::shouldReceive('any')->andReturn(false);
        Gate::shouldReceive('check')->andReturn(false);

        $task = Task::factory()->create(['user_id' => $this->mockedUsers[0]->id]);

        $response = $this->get(route('tasks.edit', ['id' => $task->id]));
        $response->assertStatus(200);
    }
}
