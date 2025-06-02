<?php

// class Task represents a task with properties like id, description, status, created_at, and updated_at
// It includes methods to create a task, update its description and status, convert to/from an array, and display task details.
class Task{
    private $id;
    private $description;
    private $status;   
    private $created_at;
    private $updated_at;

    public function __construct($id, $description, $status = "todo", $created_at = null, $updated_at = null) {
        $this->id = $id;
        $this->description = $description;
        $this->status = $status;
        $this->created_at = $created_at ?? date('Y-m-d H:i:s');
        $this->updated_at = $updated_at ?? date('Y-m-d H:i:s');
    }

    public function getId() {
        return $this->id;
    }
    public function getStatus() {
        return $this->status;
    }
    //function to create a Task object from an associative array
    // This is useful for loading tasks from a JSON file or database
    public static function fromArray(array $arr):Task {
        return new Task(
            $arr['id'],
            $arr['description'],
            $arr['status'],
            $arr['created_at'],
            $arr['updated_at']
        );
    }

    //function to convert a Task object to an associative array
    // This is useful for saving tasks to a JSON file or database
    public function toArray(): array{
        return [
            'id' => $this->id,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
    //function to update the description of a task
    public function updateDescription($description) {
        $this->description = $description;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    //function to update the status of a task
    public function updateStatus($status) {
        $this->status = $status;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    //function to display the task details
    public function display() {
        echo "Task ID: {$this->id}\n";
        echo "Description: {$this->description}\n";
        echo "Status: {$this->status}\n";
        echo "Created At: {$this->created_at}\n";
        echo "Updated At: {$this->updated_at}\n";
    }
}
// Define the file where tasks will be stored
define('TASKS_FILE', 'tasks.json');


// reads json file and returns an array of Task objects
function LoadTasks(): array{
    if (!file_exists(TASKS_FILE)) {
        file_put_contents(TASKS_FILE, json_encode([]));
    }

    //get the file content as json data
    $json = file_get_contents(TASKS_FILE);
    //decode the json data to an associative array
    $date = json_decode($json, true) ?? [];
    $tasks = [];
    //convert each associative array to a Task object
    foreach ($date as $taskData) {
        $tasks[] = Task::fromArray($taskData);
    }
    return $tasks;
}

//convert task obejcts to arrays and writes them to a json file
function saveTasks(array $tasks){
    $data = [];
    //convert each Task object to an associative array
    foreach ($tasks as $task) {
        $data[] = $task->toArray();
    }
    //encode the associative array to json format and save it to the file
    file_put_contents(TASKS_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

//function to add a new task
function addTask($description){
    $tasks = LoadTasks();
    // if there are no tasks, set the id to 1, otherwise find the maximum id and increment it by 1
    if(empty($tasks)) {
        $id = 1;
    } else {
        $maxId = 0;
        foreach ($tasks as $t) {
            if ($t->getId() > $maxId) {
                $maxId = $t->getId();
            }
        }
        $id = $maxId + 1;
    }
    $task = new Task($id, $description);
    $tasks[] = $task;
    saveTasks($tasks);
    echo "Task with id $id is added successfully.\n";
}

//function to update an existing task
function updateTask($id, $description){
    $tasks = LoadTasks();
    foreach ($tasks as $task){
        if($task->getId() == $id){
            $task->updateDescription($description);
            saveTasks($tasks);
            echo "Task with id $id is updated successfully.\n";
            return;
        }
    }
    echo "Task with id $id is not found";
}


//function to delete a task by its id
function deleteTask($id){
    $tasks = LoadTasks();
    //filter the tasks array to remove the task with the given id
    $remainingTasks = array_filter($tasks, fn($task)=> $task->getId() != $id);
    if(count($tasks) == count($remainingTasks)){
        echo "Task with id $id is not found.\n";
        return;
    }
    saveTasks($remainingTasks);
    echo "Task with id $id is deleted successfully.\n";
}

//function to mark a task as in-progress or done
function markTask($id, $status){
    $tasks = LoadTasks();

    foreach ($tasks as $task){
        if($task->getId() == $id){
            if($status == "in-progress" or $status == "done"){
                $task->updateStatus($status);
                saveTasks($tasks);
                echo "Task with id $id marked as $status\n";
                return;
            }
            else{
                echo "this is not a valid status. Valid status are [todo, in-progress, done]\n";
                return;
            }
        }
    }
    echo "Task with id $id is not found\n";
}

// function to list all tasks
function listAllTasks(){
    $tasks = LoadTasks();
    if (empty($tasks)) {
        echo "No tasks found.\n";
        return;
    }
    foreach ($tasks as $task) {
        $task->display();
    }
}

// function to list tasks with a specific status
function listSpecificTasks($requiredStatus){
    $tasks = LoadTasks();
    $tasks = array_filter($tasks, fn($task) => $task->getStatus() == $requiredStatus);
    if (empty($tasks)){
        echo "No tasks found.\n";
        return;
    }
    foreach ($tasks as $task){
        $task->display();
    }
    
}

$option = (int)readLine("Enter option (1: Add task, 2: Update task, 3: Delete task, 4: Mark task, 5: List all tasks, 6: List specific tasks, 0:exit ): ");
while($option != 0){
    switch ($option) {
        case 1:
            $description = readLine("Enter task description: ");
            addTask($description);
            break;
        case 2:
            $id = (int)readLine("Enter task ID to update: ");
            $description = readLine("Enter new task description: ");
            updateTask($id, $description);
            break;
        case 3:
            $id = (int)readLine("Enter task ID to delete: ");
            deleteTask($id);
            break;
        case 4:
            $id = (int)readLine("Enter task ID to mark: ");
            $status = readLine("Enter new status (todo, in-progress, done): ");
            markTask($id, $status);
            break;
        case 5:
            listAllTasks();
            break;
        case 6:
            $status = readLine("Enter status to filter by (todo, in-progress or done): ");
            listSpecificTasks($status);
            break;
        default:
            echo "Invalid option.\n";
    }
    $option = (int)readLine("Enter option (1: Add task, 2: Update task, 3: Delete task, 4: Mark task, 5: List tasks, 6: List specific tasks, 0:exit ): ");
}


?>