/******
The main Javascript for the page
*******/
function Main() {	
	var self = this;
	this.apiClient = new ApiClient();
	this.openEntries = [];
	
	this.loadProjects = function(){
		self.apiClient.getOpenEntry(function(data){
			self.openEntries = data;
			
			self.showProjectList();
		});
	};
	
	this.showProjectList = function()
	{
		self.apiClient.getProjects(function(data){
			$('#gridlist').off('click', 'button', self.projectButtonClick);
			
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
	
	this.projectButtonClick = function(e){
		projectid = $(e.target.closest('li')).attr('project-id')
		self.apiClient.postStartEntry(projectid, 0, function(){
			self.showProjectList();
		});
	};
	
	this.updateTimes = function(){
		$('#gridlist li .right-column button.lockTime').each(function(i, e){
			time = $(e).attr('starttime');
			$(e).html(self.getTimeDifference(new Date().getTime(), Date.parse(time)))
		});
		
		setTimeout(self.updateTimes, 2000);
	}
	
	this.getTimeDifference = function(date1, date2){
		difference = (date1 - date2)/1000;
		hours = Math.floor(difference / 3600);
		minutes = Math.floor((difference % 3600)/60);
		
		return hours + ":" + this.pad(minutes, 2);
	};
	
	this.pad = function(num, size) {
		var s = num+"";
		while (s.length < size) s = "0" + s;
		return s;
	};
	
	this.renderExternalTmpl = function(item) {
		var file = 'includes/templates/' + item.name + '.tmpl.htm';
		$.when($.get(file))
		 .done(function(tmplData) {
			 $.templates({ tmpl: tmplData });
			 $(item.selector).html($.render.tmpl(item.data));
		 });    
	};
}