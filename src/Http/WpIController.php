<?php


namespace DevStetic\WpImporter\Http;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Site;
use Input;
use Illuminate\Http\Request;
use App\DevSteticOption;
use Validator;
use Illuminate\Support\Facades\Redirect;
use Log;
use View;

class WpIController extends Controller
{
	
	public function __construct(Request $request){
	    
	    $this->middleware('auth');
		
		$dashboard_url = env("PETE_DASHBOARD_URL");
		$viewsw = "/sites";
		
		//DEBUGING PARAMS
		$debug = env('PETE_DEBUG');
		if($debug == "active"){
			$inputs = $request->all();
			Log::info($inputs);
		}
		
		$system_vars = parent::__construct();
		$pete_options = $system_vars["pete_options"];
		$sidebar_options = $system_vars["sidebar_options"];
		
		View::share(compact('dashboard_url','viewsw','pete_options','system_vars','sidebar_options'));
		
	}
  	
	public function create(){
		
		$current_user = Auth::user(); 
		$viewsw = "/import_wordpress";
		return view("wp-importer::create",compact('viewsw','current_user'));
	}
	
	
	
	public function store(Request $request)
	{
		Log::info("entro en store de WpIController");
		
		$pete_options = new DevSteticOption();
		$user = Auth::user();
		$fields_to_validator = $request->all();
		
		$site = new Site();
		$site->output = "";
		$site->user_id = $user->id;
		$site->app_name = $request->input("app_name");
		$site->action_name = "Import";
		
		$site->name = $request->input("name");
		$site->to_import_project = $request->input("to_import_project");
		$site->user_id = $user->id;
		$site->url = $request->input("url");
		$site->big_file_route = $request->input("big_file_route");
		$site->laravel_version = $request->input("selected_version");	
		
		$app_root = $pete_options->get_meta_value('app_root');
		if($pete_options->get_meta_value('domain_template')){
	
			$site->url = $site->url . "." . $pete_options->get_meta_value('domain_template');
		}
		
		$validator = Validator::make($fields_to_validator, [
			'name' =>  array('required', 'regex:/^[a-zA-Z0-9-_]+$/','unique:sites'),
			'url' => 'required|unique:sites',
		]);
		
     	if ($validator->fails()) {
			
	        return redirect('import_wordpress')
	        		->withErrors($validator)
	        			->withInput();
     	 }
		
		if($request->file('filem')!= ""){
			
			/*
			$size = $request->file('filem')->getSize();	
			
			$file_size = number_format($size / 1048576,2);
			Log::info($file_size.' MB');	
		
			if($file_size > 450){
				return Redirect::to('/import_wordpress')->withErrors(['msg' => 'This file exceeds the size (450MB) limit for an http post request please try the option: File path for large files (Optional)']);
			}
			*/
			
			$file = $request->file('filem');
	        // SET UPLOAD PATH
	        $destinationPath = 'uploads';
	         // GET THE FILE EXTENSION
	        $extension = $file->getClientOriginalExtension();
	         // RENAME THE UPLOAD WITH RANDOM NUMBER
	        $fileName = rand(11111, 99999) . '.' . $extension;
	         // MOVE THE UPLOADED FILES TO THE DESTINATION DIRECTORY
	        $upload_success = $file->move($destinationPath, $fileName);
			$site->zip_file_url = $fileName;
		
		}
		
	
		$site->import_wordpress();
			
		return Redirect::to('/sites/'.$site->id .'/edit' .'?success=' . 'true');
	}	
	
}