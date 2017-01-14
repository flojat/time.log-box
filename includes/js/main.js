/******
The main Javascript for the page
*******/
function Main() {	
	var self = this;
	this.apiClient = new ApiClient();
	this.openEntries = [];
	
	$('.footer button.standBy').click(this.standBy);
	
	/* Gets currently open entries and then loads projects */
	this.loadProjects = function(){
		//remove click event in case it was added before
		$('.footer button.standBy').off("click");
		
		self.apiClient.getOpenEntry(function(data){
			self.openEntries = data;
			
			self.showProjectList();
		});
		
		$('.footer button.standBy').click(this.standBy);
	};
	
	/* gets projects from the server and displays them */
	this.showProjectList = function()
	{
		self.apiClient.getProjects(function(data){
			//remove click event in case it was added before
			$('#gridlist').off('click', 'button', self.projectButtonClick);
			
			//transform data from the webservice to a more usable format
			data = $.map(data, function(val, i){
				activeEntry = $.grep(self.openEntries, function(e){return e.project_id == val.project_id});
				
				result = {
					projectid: val.project_id,
					projectimage: val.client_logo,
					companyname: val.client_name,
					projectname: val.project_name,
					clientprojectownername: val.client_project_owner_name,
					clientphone: val.client_project_owner_tel,
					active: activeEntry.length > 0,
					time: activeEntry.length > 0 ? self.getTimeDifference(new Date().getTime(), Date.parse(activeEntry[0].start)) : "" ,
					starttime: activeEntry.length > 0 ? activeEntry[0].start : ""
				};
				
				return result;
			});
			
			self.renderExternalTmpl({ name: 'projectListTemplate', selector: '#gridlist', data: data });
			
			$('#gridlist').on('click', 'button', self.projectButtonClick);
		});
		
		self.updateTimes();
	};
	
	/* handles clicks on the button of a project  
		e = the clicked button supplied by jQuery
	*/
	this.projectButtonClick = function(e){
		projectid = $(e.target.closest('li')).attr('project-id');
		
		self.apiClient.postStartEntry(projectid, 0, function(){
			//reload project list
			self.loadProjects();
		});
	};
	
	/* Stops all tracking 
		e = the clicked button supplied by jQuery
	*/
	this.standBy = function(e){
		//get active project
		active = $("#gridlist li[active='true']");
		
		if(active){
			projectid = active.attr('project-id');
			self.apiClient.postEntry(projectid, 1, function(){
				//reload project list
				self.loadProjects();
			});
		}
	};
	
	/* updates the times for active projects */
	this.updateTimes = function(){
		//update each active button's time
		$('#gridlist li .right-column button.lockTime').each(function(i, e){
			time = $(e).attr('starttime');
			$(e).html(self.getTimeDifference(new Date().getTime(), Date.parse(time)))
		});
		
		//call method again in 1 second
		setTimeout(self.updateTimes, 1000);
	}
	
	/*gets the difference of two dates
		date1 = the first (newer) date
		date2 = the second (older) date
		returns the difference (date1 - date2) between the dates in the hour:minute format, eg. 139:34
	*/
	this.getTimeDifference = function(date1, date2){
		difference = (date1 - date2)/1000;
		hours = Math.floor(difference / 3600);
		minutes = Math.floor((difference % 3600)/60);
		
		return hours + ":" + this.pad(minutes, 2);
	};
	
	/* pads a number with leading zeros 
		num = the number to format
		size = the length the number should be padded to
	*/
	this.pad = function(num, size) {
		var s = num+"";
		while (s.length < size) s = "0" + s;
		return s;
	};
	
	/* gets a template file and renders it with jsRender 
		item = object with parameters:
			name = name of the template file
			selector = jquery selector for the parent object the template should be appended to
			data = the data to fill the template with
	*/
	this.renderExternalTmpl = function(item) {
		var file = 'includes/templates/' + item.name + '.tmpl.htm';
		$.when($.get(file))
		 .done(function(tmplData) {
			 $.templates({ tmpl: tmplData });
			 $(item.selector).html($.render.tmpl(item.data));
		 });    
	};
}