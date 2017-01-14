/******
The main Javascript for the page
*******/
function Main() {	
	var self = this;
	this.apiClient = new ApiClient();
	this.openEntries = [];
	
	/* sets up everything for the Projects page */
	this.setupLoginPage = function(){
		$("#login").click(function(){
			var user = $("#username").val();
			var password = $("#password").val();
			
			self.apiClient.login(user, password, function(){window.location.replace("main.html");});
		});
	};
	
	/* sets up everything for the Projects page */
	this.setupProjectsPage = function(){
		self.checkLoggedIn();
		$('.footer button.standBy').click(this.standBy);
		$('#logout').click(self.logout);
		
		self.loadProjects();
	};
	
	/* sets up everything for the statistics page */
	this.setupStatisticsPage = function(){
		self.checkLoggedIn();
		
		$('#logout').click(self.logout);
		
		//initialize filter datepickers
		self.setDatepickerDate();
		
		$("#datepickerFrom").datepicker({dateFormat: "yy-mm-dd"});
		$("#datepickerTo").datepicker({dateFormat: "yy-mm-dd"});
		
		//setup buttons
		$("#reset.button").click(self.setDatepickerDate);
		$("#send.button").click(self.drawChart);
		
		//load google charts
		google.charts.load("current", {packages: ['corechart']});
		google.charts.setOnLoadCallback(self.drawChart);
	};
	
	/* gets data and draws the chart on the statistics pages */
	this.drawChart = function() {
		var from = new Date($("#datepickerFrom").val());
		var to = new Date($("#datepickerTo").val());
		
		self.apiClient.getStats({start_date: from.toJSON(), end_date: to.toJSON()}, function(statisticsData){
			var data = google.visualization.arrayToDataTable(statisticsData);

			var view = new google.visualization.DataView(data);

			var options = {
				title: "Worked actual week:",
				width: 340,
				height: 400,
				legend: {position: 'top', maxLines: 4},
				bar: {groupWidth: '75%'},
				isStacked: true,
			};

			var chart = new google.visualization.ColumnChart(document.getElementById("columnchart_values"));
			chart.draw(view, options);
		});
	}
	
	/* Sets fromdate to 1 week ago and to date to today for datepickers in statistics filter */
	this.setDatepickerDate = function(){
		var to = new Date();
		var from = new Date();
		from.setDate(from.getDate()-7);
		
		$("#datepickerFrom").val(from.getFullYear()+'-'+self.pad(from.getMonth()+1, 2)+'-'+self.pad(from.getDate(), 2));
		$("#datepickerTo").val(to.getFullYear()+'-'+self.pad(to.getMonth()+1, 2)+'-'+self.pad(to.getDate(), 2))
	};
	
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
					time: activeEntry.length > 0 ? self.getTimeDifference(new Date().getTime(), Date.parse(activeEntry[0].start.split(" ").join("T"))) : "" ,
					starttime: activeEntry.length > 0 ? activeEntry[0].start.split(" ").join("T") : ""
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
		projectid = $($(e.target).closest('li')).attr('project-id');
		
		self.apiClient.postEntry(projectid, 0, function(){
			//reload project list
			self.loadProjects();
		});
	};
	
	/* logs out the user (removes jwt) */
	this.logout = function(){
		localStorage.removeItem("jwt");
		
		window.location.replace("login.html");
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
	
	/* checks if the user has a jwt token and redirects to login page if not */
	this.checkLoggedIn = function(){
		if(localStorage.getItem("jwt") === null)
		{
			//user is not logged in, redirect
			window.location.replace("login.html");
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
		hours = Math.max(Math.floor(difference / 3600), 0);
		minutes = Math.max(Math.floor((difference % 3600)/60), 0);
		
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