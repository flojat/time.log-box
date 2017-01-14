/********
Object to access/handle the API
*******/
function ApiClient(){
	this.apiUrl = "https://log-box.ch/webec/api/";
	this.projectsEndpoint = "projects"
	this.projectEndpoint = "project"
	this.entryEndpoint = "entry"
	this.openEntryEndpoint = "openentry"
	
	/* gets a list of current projects from the server */
	this.getProjects = function(callback){
		$.ajax({
			url: this.apiUrl + this.projectsEndpoint
		}).done(function(data) {
		   callback(data);
		});
	}
	
	/* gets the currently active entries for all projects from the server */
	this.getOpenEntry = function(callback){
		$.ajax({
			url: this.apiUrl + this.openEntryEndpoint
		}).done(function(data) {
		   callback(data);
		}).fail(function(jqXHR, textStatus, errorThrown) {
		   alert( "error" );
		});
	}
	
	/* Post a new entry for a project to the server 
		projectid = the id of the project
		standby = 1 -> don't create a new entry, just close any existing one, 0 -> create a new entry
	*/
	this.postEntry = function(projectid, standby, callback) {
		$.ajax({
			url: this.apiUrl + this.projectEndpoint + "/" + projectid + "/entry",
			method: "POST",
			contentType: "application/json",
			data: {project_id: projectid, go_to_standby: standby}
		}).done(function(data) {
		   callback(data);
		}).fail(function(jqXHR, textStatus, errorThrown) {
		   alert( "error" );
		});
	}
}