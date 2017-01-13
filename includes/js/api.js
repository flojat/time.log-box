/********
Object to access/handle the API
*******/
function ApiClient(){
	this.apiUrl = "https://log-box.ch/webec/api/";
	this.projectsEndpoint = "projects"
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
}