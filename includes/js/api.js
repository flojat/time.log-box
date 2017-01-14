/********
Object to access/handle the API
*******/
function ApiClient(){
	var self = this;

	this.apiUrl = "https://log-box.ch/webec/api/";
	this.projectsEndpoint = "projects";
	this.projectEndpoint = "project";
	this.entryEndpoint = "entry";
	this.openEntryEndpoint = "openentry";
	this.statsEndpoint = "stats";
	this.loginEndpoint = "login";
	
	/* gets a list of current projects from the server */
	this.getProjects = function(callback){
		$.ajax({
			url: this.apiUrl + this.projectsEndpoint,
			beforeSend : function(xhr) {xhr.setRequestHeader("Authorization", "Bearer " + localStorage.getItem('jwt'));}
		}).done(function(data) {
		   callback(data);
		}).fail(function(jqXHR, textStatus, errorThrown) {
		   self.globalErrorHandler(jqXHR, textStatus, errorThrown);
		});
	};
	
	/* gets the currently active entries for all projects from the server */
	this.getOpenEntry = function(callback){
		$.ajax({
			url: this.apiUrl + this.openEntryEndpoint,
			beforeSend : function(xhr) {xhr.setRequestHeader("Authorization", "Bearer " + localStorage.getItem('jwt'));}
		}).done(function(data) {
		   callback(data);
		}).fail(function(jqXHR, textStatus, errorThrown) {
		   self.globalErrorHandler(jqXHR, textStatus, errorThrown);
		});
	};
	
	/* Post a new entry for a project to the server 
		projectid = the id of the project
		standby = 1 -> don't create a new entry, just close any existing one, 0 -> create a new entry
	*/
	this.postEntry = function(projectid, standby, callback) {
		$.ajax({
			url: this.apiUrl + this.projectEndpoint + "/entry",
			method: "POST",
			contentType: "application/json",
			dataType: "json",
			data: JSON.stringify({project_id: projectid, go_to_standby: standby}),
			beforeSend : function(xhr) {xhr.setRequestHeader("Authorization", "Bearer " + localStorage.getItem('jwt'));}
		}).done(function(data) {
		   callback(data);
		}).fail(function(jqXHR, textStatus, errorThrown) {
		   self.globalErrorHandler(jqXHR, textStatus, errorThrown);
		});
	};
	
	/* gets time stats for the current user */
	this.getStats = function(params, callback){
		$.ajax({
			url: this.apiUrl + this.statsEndpoint + "/week",
			data: params,
			beforeSend : function(xhr) {xhr.setRequestHeader("Authorization", "Bearer " + localStorage.getItem('jwt'));}
		}).done(function(data) {
		   callback(data);
		}).fail(function(jqXHR, textStatus, errorThrown) {
		   self.globalErrorHandler(jqXHR, textStatus, errorThrown);
		});
	};
	
	/* login to page, returns jwt */
	this.login = function(user, password, callback){
		$.ajax({
			url: this.apiUrl + this.loginEndpoint,
			method: "POST",
			contentType: "application/json",
			dataType: "json",
			data: JSON.stringify({user: user, password: password})
		}).done(function(data) {
			localStorage.setItem("jwt", data.token);
		   callback();
		}).fail(function(jqXHR, textStatus, errorThrown) {
		   self.globalErrorHandler(jqXHR, textStatus, errorThrown);
		});
	};
	
	/* used to handle common request errors */
	this.globalErrorHandler = function(jqXHR, textStatus, errorThrown){
		switch(jqXHR.status){
			case 401:
				//unauthorized --> wrong/expired login
				window.location.replace("login.html");
			default:
				alert( "error: " + errorThrown );
		}
	};
}