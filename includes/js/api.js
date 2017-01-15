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
			cache: false,
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
		message = message to add to a stop entry, if any
	*/
	this.postEntry = function(projectid, standby, message, callback) {
		$.ajax({
			url: this.apiUrl + this.projectEndpoint + "/entry",
			method: "POST",
			contentType: "application/json",
			dataType: "json",
			data: JSON.stringify({project_id: projectid, go_to_standby: standby, message: message}),
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
			url: this.apiUrl + this.statsEndpoint,
			cache: false,
			data: params,
			beforeSend : function(xhr) {xhr.setRequestHeader("Authorization", "Bearer " + localStorage.getItem('jwt'));}
		}).done(function(data) {
		   callback(data);
		}).fail(function(jqXHR, textStatus, errorThrown) {
		   self.globalErrorHandler(jqXHR, textStatus, errorThrown);
		});
	};
	
	/* gets time stats for the current user and current week */
	this.getStatsWeek = function(callback){
		$.ajax({
			url: this.apiUrl + this.statsEndpoint + '/week',
			cache: false,
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
			cache: false,
			method: "POST",
			contentType: "application/json",
			dataType: "json",
			data: JSON.stringify({user: user, password: password})
		}).done(function(data) {
			localStorage.setItem("jwt", data.token);
		    callback();
		}).fail(function(jqXHR, textStatus, errorThrown) {
		    if(jqXHR.status == 401){
				alert("Error: Login Failed, wrong username or password");
			}else{
				self.globalErrorHandler(jqXHR, textStatus, errorThrown);
			}
		});
	};
	
	/* used to handle common request errors */
	this.globalErrorHandler = function(jqXHR, textStatus, errorThrown){
		switch(jqXHR.status){
			case 401:
				//unauthorized --> wrong/expired login
				window.location.replace("login.html");
				break;
			case 500:
				alert("Server Error: Please try again later or contact an administrator if the problem persists");
				break;
			case 403:
				alert("Error: Not enough rights to access this resource");
				break;
			case 404:
				alert("Error: The requested resource was not found");
				break;
			case 400:
				alert("Error: Bad Server Request");
				break;
			default:
				alert( "error: " + errorThrown );
		}
	};
}