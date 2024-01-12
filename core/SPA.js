var SPA = (function() {
	function fSPA() {
		_SPA = this;
		
		this.fReady = [];
		this.isReady = false;
		
		this.fUnload = [];
		
		this.screen_size = '';
		this.fResize = {xs: [], sm: [], md: [], lg: [], xl: [], xxl: []};
		
		$(document).on("click", ".ajaxAction", function (e) {
			e.preventDefault();
			var page = $(e.currentTarget).attr("href");
			_SPA.ajaxAction(page, !$(e.currentTarget).hasClass("noPreloader"));
		});
		
		$(window).on('resize', function() {
			if ($(window).width() >= 1400) {
				s_size = 'xxl';
			} else if ($(window).width() >= 1200) {
				s_size = 'xl';
			} else if ($(window).width() >= 992) {
				s_size = 'lg';
			} else if ($(window).width() >= 768) {
				s_size = 'md';
			} else if ($(window).width() >= 576) {
				s_size = 'sm';
			} else {
				s_size = 'xs';
			}
			if (!(this.screen_size==s_size)) {
				this.screen_size = s_size;
				_SPA.fResize[s_size].forEach((i) => { i.f.call(); })
			}
		});
		window.dispatchEvent(new Event('resize'));

		//Manejar cambios de tamaño. Ejecutar una serie de funciones para cada rango de tamaños
		//Los tamaños posibles son:
		// xs: <576
		// sm: >576
		// md: >768
		// lg: >992
		// xl: >1200
		// xxl:>1400
		this.onResize = function (size, mod, inF) {
			this.fResize[size].push({m: mod, f: inF});
		}
		
		this.ready = function() {
			while (this.fReady.length) {
				this.fReady[0].call();
				this.fReady.shift();
			}
			this.isReady = true;
		}
		
		this.onUnload = function(inF) {this.fUnload.push(inF);}
		
		this.unloadMod = function () {
			while (this.fUnload.length) {
				this.fUnload[0].call();
				this.fUnload.shift();
			}
		}
		
		this.whenReady = function(inF) {
			if (this.isReady == false) {
				this.fReady.push(inF);
			} else {
				inF.call();
			}
		}
		
		this.updTooltips = function() {
			var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
			var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
				return new bootstrap.Tooltip(tooltipTriggerEl, {trigger: 'hover'})
			});
		}
		
		//Función SPA: Mensajes
		//  Los tipos de mensaje son:
		//    0: Info
		//    1: Success
		//    2: Warning
		//    3: Error
		this.notify = function(t, m=0) {
			switch(m) {case 0: m="secondary"; break; case 1: m="success"; break; case 3: m="danger"; break;}
			$('#notifications').prepend('<div class="alert with-close alert-'+m+' alert-dismissible fade show" role="alert">'+t+'<button type="button" class="btn-close" data-bs-dismiss="alert"><span>×</span></button></div>');
		}
		
		this.chgPage = function (title, html) {
			_SPA.unloadMod();
			$("#c-body").empty();
			$("#c-body").html(html);
			document.title = title + " | CW911";
			$(window).scrollTop(0);
		}
		
		this.chgContent = function (url, title, icon, html) {
			_SPA.unloadMod();
			$("#content").empty();
			$("#content").html(html);
			$('#page-title').empty();
			$('#page-title').append('<i class="fa-fade menu-icon fa fa-' + icon + ' " style="--fa-animation-duration: 2s;"></i>&nbsp;' + title);
			document.title = title + " | CW911";
			_SPA.currModule = url.split('module=')[1].split('&')[0];
			_SPA.baseModule = _SPA.currModule.split(".")[0];
			//Se cambió de modulo, por lo que hay que activar el ítem que lo invocó.
			$("#menu a").removeClass("active");
			$("#menu a").each(function () {
				if ($(this).attr('href') == url) {
					$(this).addClass("active");
				}
			});
			if (sidebar._isShown) sidebar.hide();
			$(window).scrollTop(0);
		}
		
		this.handleJSON = function(json, url=null) {
			if (typeof json.msgStack == "object") { json.msgStack.forEach((msg) => { _SPA.notify(msg.text, msg.type); }) }
			if (json.modPage) { _SPA.chgPage(json.modTitle, json.modPage); }
			if (json.modContent) { _SPA.chgContent(url, json.modTitle, json.modIcon, json.modContent); }
		}
		
		this.get = function(u, args) {
			url=u+"&QID="+this.SSEQID;
			$.get(url, function (response) {
				_SPA.handleJSON(response);
				if (args && typeof args.success == 'function') {args.success.call(0, response)}
			}, 'json')
			.fail(function() {
				_SPA.notify('Fallo al intentar conectarse al servidor.', "error");
				if (args && typeof args.fail == 'function') {args.fail.call()}
			});
		}
		
		this.post = function(u, postData, args) {
			url=u+"&QID="+this.SSEQID;
			$.post(url, postData, function (response) {
				_SPA.handleJSON(response);
				if (args && typeof args.success == 'function') {args.success.call(0, response)}
			}, 'json')
			.fail(function() {
				_SPA.notify('Fallo al intentar conectarse al servidor.', "error");
				if (args && typeof args.fail == 'function') {args.fail.call()}
			});
		}
		
		this.ajaxAction = async function (u, p=true) {
			url=u+"&QID="+_SPA.SSEQID;
			if (p) preloader.show({});
			$.getJSON(url, function( data ) {//Se recibe una respuesta en JSON
				SPA.handleJSON(data, url);
				if (data['status'] == "ERR") {
					switch (data['statusCode']) {
						case "not-logged":
							getPage("code/body.login.php?code=1");
							break;
					}
					preloader.hide();
				} else if (data['status'] == "OK") {
					preloader.hide();
				}
			})
			.fail(function() { //No se recibio una respuesta JSON valida
				preloader.hide();
				SPA.notify("Error al cargar módulo.", "error");
			});
		}
		
		// Objeto y funciones EventSource, si está soportado por el navegador
		if (window.EventSource) {
			this.evtSource = null;
			this.fSSE = {};
			this.SSEQID = null;
			
			this.stopSSE = function() {
				if (this.evtSource != null) {
					this.evtSource.close();
					this.evtSource = null;
					_SPA.SSEQID = null;
				}
			}
			
			this.startSSE = function() {
				if (this.evtSource != null) this.evtSource.close();
				this.evtSource = new EventSource('?req=SSE&QID='+_SPA.SSEQID);
				
				this.evtSource.onmessage = function(event) {
					evt = JSON.parse(event.data);
					
					try {
						if (evt=="ping") return;
						if (_SPA.fSSE.hasOwnProperty(evt.func)) {
							_SPA.fSSE[evt.func].call(this, evt.data);
						}
					} catch (err) {
						console.log(err);
						return;
					}
				}
				
				this.evtSource.onerror = function(event) {
					_SPA.startSSE(_SPA.SSEQID);
					_SPA.notify('Pérdida de conexión al servidor (SSE). Reconectando...', 3);
				}
			}
			
			this.AddSSEListener = function (name, f) {
				this.fSSE[name] = f;
			}
			
			this.DelSSEListener = function (name) {
				delete this.fSSE[name];
			}
			
			//Manejador de eventos para SPA
			this.AddSSEListener("SPA", function(m) {
				if (m.command == "SetQID") { _SPA.SSEQID = m.QID;						//QID del servidor SSE
				} else if (m.command == "shutdown") {_SPA.stopSSE();					//Orden: Apagar SSE
				} else if (m.command == "message") { _SPA.notify(m.msg, m.type); }		//Orden: Mensaje
			});
		}
	}
	return new fSPA;
})();

	var preloader = (function ()
	{
		function preloader()
		{
			var _preloader = this;
			this.firstLoad = true;
			this.oneText = false;
			this.defaultText = "Cargando...";
			this.log = function (t) { //console.log("preLoader: " + t);
			};
			this.update = function ({text = "-1", progress = "-1", mode = "-l"}) 
			{
				if (!(text == "-1")) { $('#loadText').text(text);}
				if (!(progress == "-1")) { $('#loadText').text(text + " (" + progress + "%)"); $('#loadBar').animate({width : Math.floor(progress) + '%'},30); }
				if (mode == "error") { $('#loadBar').animate({backgroundColor : "#c70c0c"},300); 
				} else if (mode == "load") { $('#loadBar').animate({backgroundColor : "#0d6efd"},300);}
				
				_preloader.log("Updated text: " + text + ", progress: " + progress + ", mode: " + mode);
			};
			this.hide = function (delay=100) { 
				if (!this.firstLoad) {jQuery('#preloader').delay(delay).hide();
				} else {jQuery('#preloader').delay(delay).fadeOut('slow');}
				if (this.oneText) {$('#loadText').text(this.defaultText); this.oneText=false;}
				if (this.firstLoad) {
					this.firstLoad=false; 
					$('#preloader').delay(500 + delay).queue(function (next) {
						$(this).append($('<div/>').addClass("preloader-top sticky-top start-50 translate-middle rounded-bottom").css("height", "80px").css("width", "320px").css("background-color", "#6c757de6"));
						$(".Loading").detach().appendTo(".preloader-top");
						$(this).css('background-color', 'rgba(77, 89, 95, 0.4)');
						$(".Loading").css("width", "300px").css("margin", "5px 0 0 -150px");
						$("#preloader #status").remove();
						$("#preloader #loadlogo").remove();
						$("#preloader #loadText").text(this.defaultText).css("color", "#ffffff").css("text-overflow", "ellipsis").css("overflow", "hidden").css("white-space", "nowrap");
						$("#preloader #loadBar").addClass("progress-bar progress-bar-striped progress-bar-animated");
						next();
					});
				};
			};
		this.show = function ({text="-1", progress="-1"}) {
				if (!(text == "-1")) { $('#loadText').text(text); this.oneText=true;}
				if (!(progress == "-1")) { $('#loadText').text(text + " (" + progress + "%)"); $('#loadBar').animate({width : Math.floor(progress) + '%'},30); }
				if (!this.firstLoad) {jQuery('#preloader').show();
				} else {jQuery('#preloader').fadeIn(100);};
			};
			this.loadErr = function () {_preloader.update({text: "Error al cargar la aplicación. Compruebe el acceso de red al sistema.", mode: "error"})};
		}
		return new preloader;
	})();
	
	class FormCreator {
		a(itemData) {
			//Devolver un elemento HTML basado en los argumentos
			const item = document.createElement(itemData.itemType);
			if (itemData.classes) item.classList.add(...itemData.classes);
			if (itemData.text) item.innerText = itemData.text;
			if (itemData.id) item.setAttribute('id', itemData.id);
			if (itemData.for) item.setAttribute('for', itemData.for);
			if (itemData.type) item.setAttribute('type', itemData.type);
			if (itemData.name) item.setAttribute('name', itemData.name);
			if (itemData.readonly) item.setAttribute('readonly', "");
			if (itemData.multiple) item.setAttribute('multiple', "");
			if (itemData.value) item.setAttribute('value', itemData.value);
			if (itemData.required) item.setAttribute('required', "");
			if (itemData.role) item.setAttribute('role', itemData.role);
				
			return item;
		}
		
		sw(itemData) {
			// Devolver un switch
			return $(
				this.a({ itemType: "div", classes: ["form-check", "form-switch"] }))
					.append(this.a({ itemType: "input", type: "checkbox", role: "switch", classes: ["form-check-input"], ...itemData}))
					.append(this.a({ itemType: "label", for: itemData.id, text: itemData.label, classes: ["form-check-label"] }));
		}
			
		f(fieldData) {
			// Devolver una fila con 2 columnas: la etiqueta y el objeto
			return $(
				this.a({ itemType: "div", classes: ["form-group", "row"] }))
					.append(this.a({ itemType: "label", for: fieldData.id, text: fieldData.label, classes: ["form-label", "col", "col-form-label"] }))
					.append($(this.a({ itemType: "div", classes: ["col-sm-10"] }))
						.append(this.a(fieldData))
					);
		}
	}
	var formCreator = new FormCreator();
	
	const cssRes = [<?php getRes("assets/css", "css");?>];
	const jsRes = [<?php getRes("assets/js", "js");?>];

	var assetLoader = (function ()
	{
		function assetLoader(files)
		{
			var _assetLoader = this;
			this.log = function (t)
			{
				//console.log("ScriptLoader: " + t);
			};
			this.sLoad = function (fType, fName) 
			{
				this.pendingTasks--; percentage = Math.floor((100 / this.tasks) * (this.tasks - this.pendingTasks));
				if (this.pendingTasks == 0){
					console.log(`All assets loaded. (${Date.now() - loadStart}ms)`);
					SPA.ready();
					preloader.hide();
				}
					_assetLoader.log(this.tasks - this.pendingTasks + '/' + this.tasks + ' - Loaded ' + fType + ' "' + fName + '".');
					preloader.update({text: "Cargando...", progress: percentage});
			};
			this.loadStyle = function (filename)
			{
				// HTMLLinkElement
				var link = document.createElement("link");
				link.rel = "stylesheet";
				link.type = "text/css";
				link.href = filename;
				_assetLoader.log('Loading style ' + filename);
				link.onload = function ()
				{
					_assetLoader.sLoad("style", _assetLoader.m_css_files[i]);
				};
				link.onerror = function () { _assetLoader.log('Error loading style "' + filename + '".'); preloader.loadErr(); };
				_assetLoader.m_head.appendChild(link);
			};
			this.loadScript = function (i)
			{
				var script = document.createElement('script');
				script.type = 'text/javascript';
				script.src = _assetLoader.m_js_files[i];
				var loadNextScript = function ()
				{
					if (i + 1 < _assetLoader.m_js_files.length)
					{
						_assetLoader.loadScript(i + 1);
					}
				};
				script.onload = function ()
				{
					_assetLoader.sLoad("script", _assetLoader.m_js_files[i]);
					loadNextScript();
				};
				script.onerror = function () { _assetLoader.log('Error loading script "' + _assetLoader.m_js_files[i] + '".'); preloader.loadErr();};
				_assetLoader.log('Loading script "' + _assetLoader.m_js_files[i] + '".');
				_assetLoader.m_head.appendChild(script);
			};
			this.loadFiles = function ()
			{
				this.log("Load CSS files " + this.m_css_files);
				this.log("Load JS files " + this.m_js_files);
				for (var i = 0; i < _assetLoader.m_css_files.length; ++i)
					_assetLoader.loadStyle(_assetLoader.m_css_files[i]);
					_assetLoader.loadScript(0);
				};
			this.m_js_files = [];
			this.m_css_files = [];
			this.m_head = document.getElementsByTagName("head")[0];
			this.pendingTasks = 0;
			this.tasks = 0;
			// this.m_head = document.head; // IE9+ only
			function endsWith(str, suffix)
			{
				if (str === null || suffix === null)
					return false;
				return str.indexOf(suffix, str.length - suffix.length) !== -1;
			}
			for (var i = 0; i < files.length; ++i)
			{
				if (endsWith(files[i], ".css"))
				{
					this.m_css_files.push(files[i]);
					this.tasks++; this.pendingTasks++;
				}
				else if (endsWith(files[i], ".js"))
				{
					this.m_js_files.push(files[i]);
					this.tasks++; this.pendingTasks++;
				}
				else
					this.log('Error unknown filetype "' + files[i] + '".');
			}
			console.log("Download tasks from assetLoader: " + this.pendingTasks);
		}
		return assetLoader;
	})();

	var ScriptLoader = new assetLoader(jsRes.concat(cssRes));
	ScriptLoader.loadFiles();
	
$("#spaJS").remove();