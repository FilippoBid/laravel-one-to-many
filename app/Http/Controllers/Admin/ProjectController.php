<?php

namespace App\Http\Controllers\Admin;



use App\Models\Admin\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\Stmt\If_;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $projects= Project::all();

        return view("admin.projects.index", [
            "projects" => $projects    
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("admin.projects.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       $data = $request->validate([
        "name" => "required|string|max:20",
        "description" => "required|string",
        "cover_img" => "file",
        "github_link" => "string"
        ]);


        if (key_exists("cover_img", $data)){

            $path = Storage::put("projects", $data["cover_img"]);
        }
 
       $project = Project::create([
        ...$data,
        //a bd vado a salvare solamente il percorso 
        "cover_img" => $path ?? '',
        // recuperiamo l'id dagli user cioé user_id é uguale all'utente loggato
        "user_id" => Auth::id() 
        ]);

        return redirect()->route("admin.projects.show", compact("project"));

       
    }
       
   

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Admin\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $project = Project::findOrFail($id);
        return view("admin.projects.show", compact("project"));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Admin\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $project = Project::findOrFail($id);
        return view("admin.projects.edit",compact("project"));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $data = $request->validate([
            "name" => "required|string|max:20",
            "description" => "required|string",
            "cover_img" => "file",
            "github_link" => "string"
            ]);



        // carico il file SOLO se ne ricevo uno
        if (key_exists("cover_img", $data)) {
            // carico il nuovo file
            // salvo in una variabile temporanea il percorso del nuovo file
            $path = Storage::put("project", $data["cover_img"]);

            // Dopo aver caricato la nuova immagine, PRIMA di aggiornare il db,
            // cancelliamo dallo storage il vecchio file.
            // $post->cover_img // vecchio file
            Storage::delete($project->cover_img);
        }
        $project->update([
            ...$data,
            "user_id" =>Auth::id(),
            "cover_img"=>$path ?? $project->cover_img,

        ]);   


        return redirect()->route("admin.projects.show",$id);
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Admin\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $project = Project::findOrFail($id);

      /*   if ($project->cover_img) {
            Storage::delete($project->cover_img);
        } */

        $project->delete();
        return redirect()->route("admin.projects.index");
    }
}
