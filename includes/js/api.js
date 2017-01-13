/********
Object to access/handle the API
*******/
function ApiClient(){
	this.apiUrl = "https://log-box.ch/webec/api/";
	this.projectsEndpoint = "projects"
	this.projectEndpoint = "project"
	this.entryEndpoint = "entry"
	this.openEntryEndpoint = "openentry"
	
	this.getProjects = function(callback){
		$.ajax({
			url: this.apiUrl + this.projectsEndpoint
		}).done(function(data) {
		   callback(data);
		});
	}
	
	this.getOpenEntry = function(callback){
		$.ajax({
			url: this.apiUrl + this.openEntryEndpoint
		}).done(function(data) {
		   callback(data);
		}).fail(function(jqXHR, textStatus, errorThrown) {
		   alert( "error" );
		});
	}
	
	this.postStartEntry = function(projectid, standby, callback) {
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
	
	this.postStopEntry = function(projectid, standby, callback) {
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