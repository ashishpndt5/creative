<?php

namespace App\Console\Commands;
//namespace App\Http\Controllers;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use App\Http\Controllers\HomeController;

class HourlyUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    //protected $signature = 'command:name';
	//protected $signature = 'hour:update';
	protected $signature = 'hour:update {email} {name}';
	protected $description = 'Command description';
	//protected $signature = 'HourlyUpdate:sender {email}';
    /**
     * The console command description.
     *
     * @var string
     */
    //protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(HomeController $home)
    {
		 $email = $this->argument('email');
		 echo 'Email : '.$email;
		 
		 $name = $this->argument('name');
		 echo 'name : '.$name;
         $user = User::all();
         
         $controller = new HomeController(); // make sure to import the controller
         $controller->show($email,$name);
         
		//$user = DB::table('users')->all();
       /*foreach ($user as $a) {
		   echo $a->name;
	   }*/
	   //$app()->call('App\Http\Controllers\HomeController@show')->everyMinute();
	  // $email = $this->argument('email');
	   //echo $email;
	   //return $home->show();
	  // DB::table('users')->insert([['name' => 'paakumar','email' => 'paakumar@example.com', 'password' => '$2y$10$A66EuUB6EUtZlHDM5rK0eeDJBN/rHAhSEkDilA6lYXwtUqLFR5wOpp']]);
	   $this->info('Hourly Update has been send successfully ');
	  //$user = User::where('id',3)->delete();
    }
}
