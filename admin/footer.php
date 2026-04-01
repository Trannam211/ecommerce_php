		</div>

	</div>

	<script src="js/jquery-2.2.4.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
	<script src="js/bootstrap5-legacy-bridge.js"></script>
	<script src="js/jquery.dataTables.min.js"></script>
	<script src="js/dataTables.bootstrap.min.js"></script>
	<script src="js/select2.full.min.js"></script>
	<script src="js/jquery.inputmask.js"></script>
	<script src="js/jquery.inputmask.date.extensions.js"></script>
	<script src="js/jquery.inputmask.extensions.js"></script>
	<script src="js/moment.min.js"></script>
	<script src="js/bootstrap-datepicker.js"></script>
	<script src="js/icheck.min.js"></script>
	<script src="js/fastclick.js"></script>
	<script src="js/jquery.sparkline.min.js"></script>
	<script src="js/jquery.slimscroll.min.js"></script>
	<script src="js/jquery.fancybox.pack.js"></script>
	<script src="js/app.min.js"></script>
	<script src="js/jscolor.js"></script>
	<script src="js/on-off-switch.js"></script>
    <script src="js/on-off-switch-onload.js"></script>
    <script src="js/clipboard.min.js"></script>
	<script src="js/demo.js"></script>
	<script src="js/summernote.js"></script>

	<script>
		// Fallback (no-jQuery) handler for confirm-delete.
		// Ensures clicking "Xóa" always navigates to the stored delete URL.
		(function () {
			var STORAGE_KEY = 'admin-delete-return-state';

			function safeSetReturnState(partial) {
				try {
					var state = partial || {};
					if (state.path == null) {
						state.path = window.location.pathname;
					}
					if (state.scrollY == null) {
						state.scrollY = window.scrollY || window.pageYOffset || 0;
					}
					sessionStorage.setItem(STORAGE_KEY, JSON.stringify(state));
				} catch (e) {
					// ignore
				}
			}

			function tryRestoreReturnState() {
				try {
					var params = new URLSearchParams(window.location.search || '');
					var isDeleteReturn = params.get('success') === 'deleted' || params.get('error') === 'delete_failed';
					if (!isDeleteReturn) {
						return;
					}
					var raw = sessionStorage.getItem(STORAGE_KEY);
					if (!raw) {
						return;
					}
					sessionStorage.removeItem(STORAGE_KEY);
					var state = JSON.parse(raw) || {};
					if (state.path && state.path !== window.location.pathname) {
						return;
					}
					var y = Number(state.scrollY || 0);
					window.setTimeout(function () {
						window.scrollTo(0, y);
					}, 0);
				} catch (e) {
					// ignore
				}
			}

			function closestMatch(el, selector) {
				while (el && el.nodeType === 1) {
					if (el.matches && el.matches(selector)) {
						return el;
					}
					el = el.parentElement;
				}
				return null;
			}

			function setDeleteHref(href) {
				var modal = document.getElementById('confirm-delete');
				if (!modal) {
					return;
				}
				modal.dataset.deleteHref = href || '';
				var ok = modal.querySelector('.btn-ok');
				if (ok) {
					ok.setAttribute('href', href || '#');
					ok.setAttribute('data-href', href || '');
				}
			}

			document.addEventListener('click', function (e) {
				var trigger = closestMatch(e.target, '[data-target="#confirm-delete"], [data-bs-target="#confirm-delete"]');
				if (trigger) {
					var href = trigger.getAttribute('data-href') || trigger.dataset.href || '';
					setDeleteHref(href);
					return;
				}

				var okBtn = closestMatch(e.target, '#confirm-delete .btn-ok');
				if (okBtn) {
					var modal = document.getElementById('confirm-delete');
					var hrefOk = okBtn.getAttribute('href') || okBtn.getAttribute('data-href') || (modal ? (modal.dataset.deleteHref || '') : '');
					if (!hrefOk || hrefOk === '#') {
						e.preventDefault();
						alert('Không lấy được link xóa. Bạn hãy tải lại trang (Ctrl+F5) và thử lại.');
						return;
					}
					safeSetReturnState({
						path: window.location.pathname,
						scrollY: window.scrollY || window.pageYOffset || 0
					});
					e.preventDefault();
					window.location.href = hrefOk;
				}
			}, true);

			// Attempt restore when returning from delete.
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', tryRestoreReturnState);
			} else {
				tryRestoreReturnState();
			}
		})();

		function formatThousandsInput(value) {
			var digitsOnly = String(value || '').replace(/\D/g, '');
			if (!digitsOnly) {
				return '';
			}
			return digitsOnly.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
		}

		$(document).ready(function() {
	        $('#editor1').summernote({
	        	height: 300
	        });
	        $('#editor2').summernote({
	        	height: 300
	        });
	        $('#editor3').summernote({
	        	height: 300
	        });
	        $('#editor4').summernote({
	        	height: 300
	        });
	        $('#editor5').summernote({
	        	height: 300
	        });

	        $('.currency-input').each(function() {
	        	$(this).val(formatThousandsInput($(this).val()));
	        });

	        $(document).on('input', '.currency-input', function() {
	        	$(this).val(formatThousandsInput($(this).val()));
	        });
	    });
		$(".top-cat").on('change',function(){
			var id=$(this).val();
			var dataString = 'id='+ id;
			$.ajax
			({
				type: "POST",
				url: "get-mid-category.php",
				data: dataString,
				cache: false,
				success: function(html)
				{
					$(".mid-cat").html(html);
				}
			});			
		});
		$(".mid-cat").on('change',function(){
			var id=$(this).val();
			var dataString = 'id='+ id;
			$.ajax
			({
				type: "POST",
				url: "get-end-category.php",
				data: dataString,
				cache: false,
				success: function(html)
				{
					$(".end-cat").html(html);
				}
			});			
		});
	</script>

	<script>
	  $(function () {
		// --- Confirm delete modal wiring (must be resilient) ---
		var STORAGE_KEY = 'admin-delete-return-state';
		function setReturnStateWithDataTable() {
			try {
				var state = {
					path: window.location.pathname,
					scrollY: window.scrollY || window.pageYOffset || 0
				};
				if (window.jQuery && $.fn && $.fn.DataTable && $('#example1').length) {
					var dt = $('#example1').DataTable();
					if (dt) {
						state.dtPage = dt.page();
						state.dtLen = dt.page.len();
					}
				}
				sessionStorage.setItem(STORAGE_KEY, JSON.stringify(state));
			} catch (e) {
				// ignore
			}
		}

		function restoreReturnStateWithDataTable() {
			try {
				var params = new URLSearchParams(window.location.search || '');
				var isDeleteReturn = params.get('success') === 'deleted' || params.get('error') === 'delete_failed';
				if (!isDeleteReturn) {
					return;
				}
				var raw = sessionStorage.getItem(STORAGE_KEY);
				if (!raw) {
					return;
				}
				sessionStorage.removeItem(STORAGE_KEY);
				var state = JSON.parse(raw) || {};
				if (state.path && state.path !== window.location.pathname) {
					return;
				}
				if (window.jQuery && $.fn && $.fn.DataTable && $('#example1').length && typeof state.dtPage !== 'undefined') {
					var dt = $('#example1').DataTable();
					if (dt) {
						if (state.dtLen) {
							dt.page.len(state.dtLen);
						}
						dt.page(state.dtPage).draw(false);
					}
				}
				var y = Number(state.scrollY || 0);
				setTimeout(function () {
					window.scrollTo(0, y);
				}, 0);
			} catch (e) {
				// ignore
			}
		}

		restoreReturnStateWithDataTable();
		function resolveDeleteHref($trigger) {
			if(!$trigger || !$trigger.length) {
				return '';
			}
			// Prefer explicit data-href, fallback to href if it isn't '#'
			var dataHref = $trigger.attr('data-href') || $trigger.data('href') || '';
			if(dataHref) {
				return dataHref;
			}
			var href = $trigger.attr('href') || '';
			if(href && href !== '#') {
				return href;
			}
			return '';
		}

		// When a trigger is clicked, store delete URL immediately.
		$(document).on('click', '[data-target="#confirm-delete"], [data-bs-target="#confirm-delete"]', function() {
			var href = resolveDeleteHref($(this));
			$('#confirm-delete').data('delete-href', href || '');
			$('#confirm-delete .btn-ok').attr('href', href || '#').attr('data-href', href || '');
		});

		// When modal is shown, also try to pick URL from relatedTarget (Bootstrap provides it).
		$('#confirm-delete').on('show.bs.modal', function(e) {
			var $related = e.relatedTarget ? $(e.relatedTarget) : null;
			var href = resolveDeleteHref($related) || $(this).data('delete-href') || '';
			$(this).data('delete-href', href || '');
			$(this).find('.btn-ok').attr('href', href || '#').attr('data-href', href || '');
		});

		// Force navigate on confirm click (works even if href is set late)
		$(document).on('click', '#confirm-delete .btn-ok', function(e) {
			var href = $(this).attr('href') || $(this).data('href') || $('#confirm-delete').data('delete-href') || '';
			if(!href || href === '#') {
				e.preventDefault();
				alert('Không lấy được link xóa. Bạn hãy tải lại trang (Ctrl+F5) và thử lại.');
				return false;
			}
			setReturnStateWithDataTable();
			e.preventDefault();
			window.location.href = href;
			return false;
		});

	    //Initialize Select2 Elements
		if ($.fn.select2) {
	    	$(".select2").select2();
		}

	    //Datemask dd/mm/yyyy
		if ($.fn.inputmask) {
	    	$("#datemask").inputmask("dd-mm-yyyy", {"placeholder": "dd-mm-yyyy"});
	    	//Datemask2 mm/dd/yyyy
	    	$("#datemask2").inputmask("mm-dd-yyyy", {"placeholder": "mm-dd-yyyy"});
	    	//Money Euro
	    	$("[data-mask]").inputmask();
		}

	    //Date picker
		if ($.fn.datepicker) {
	    	$('#datepicker').datepicker({
	    	  autoclose: true,
	    	  format: 'dd-mm-yyyy',
	    	  todayBtn: 'linked',
	    	});

	    	$('#datepicker1').datepicker({
	    	  autoclose: true,
	    	  format: 'dd-mm-yyyy',
	    	  todayBtn: 'linked',
	    	});
		}

	    //iCheck for checkbox and radio inputs
		if ($.fn.iCheck) {
	    	$('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
	    	  checkboxClass: 'icheckbox_minimal-blue',
	    	  radioClass: 'iradio_minimal-blue'
	    	});
	    	//Red color scheme for iCheck
	    	$('input[type="checkbox"].minimal-red, input[type="radio"].minimal-red').iCheck({
	    	  checkboxClass: 'icheckbox_minimal-red',
	    	  radioClass: 'iradio_minimal-red'
	    	});
	    	//Flat red color scheme for iCheck
	    	$('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
	    	  checkboxClass: 'icheckbox_flat-green',
	    	  radioClass: 'iradio_flat-green'
	    	});
		}



		if ($.fn.DataTable) {
			$("#example1").DataTable();
			$('#example2').DataTable({
			  "paging": true,
			  "lengthChange": false,
			  "searching": false,
			  "ordering": true,
			  "info": true,
			  "autoWidth": false
			});
		}
		
		$('#confirm-approve').on('show.bs.modal', function(e) {
	      $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
	    });
 
	  });

		function confirmDelete()
	    {
	        return confirm("Bạn có chắc chắn muốn xóa dữ liệu này không?");
	    }
	    function confirmActive()
	    {
	        return confirm("Bạn có chắc chắn muốn kích hoạt mục này không?");
	    }
	    function confirmInactive()
	    {
	        return confirm("Bạn có chắc chắn muốn ẩn mục này không?");
	    }

	</script>

	<script type="text/javascript">
		function showDiv(elem){
			if(elem.value == 0) {
		      	document.getElementById('photo_div').style.display = "none";
		      	document.getElementById('icon_div').style.display = "none";
		   	}
		   	if(elem.value == 1) {
		      	document.getElementById('photo_div').style.display = "block";
		      	document.getElementById('photo_div_existing').style.display = "block";
		      	document.getElementById('icon_div').style.display = "none";
		   	}
		   	if(elem.value == 2) {
		      	document.getElementById('photo_div').style.display = "none";
		      	document.getElementById('photo_div_existing').style.display = "none";
		      	document.getElementById('icon_div').style.display = "block";
		   	}
		}
		function showContentInputArea(elem){
		   if(elem.value == 'Full Width Page Layout') {
		      	document.getElementById('showPageContent').style.display = "block";
		   } else {
		   		document.getElementById('showPageContent').style.display = "none";
		   }
		}
	</script>

	<script type="text/javascript">

        $(document).ready(function () {

			$("#btnAddNew").click(function () {

		        var rowNumber = $("#ProductTable tbody tr").length;

		        var trNew = "";              

		        var addLink = "<div class=\"upload-btn" + rowNumber + "\"><input type=\"file\" name=\"photo[]\"  style=\"margin-bottom:5px;\"></div>";
		           
				var deleteRow = "<a href=\"javascript:void()\" class=\"delete-photo-row btn btn-danger btn-xs\">Xóa</a>";

		        trNew = trNew + "<tr> ";

		        trNew += "<td>" + addLink + "</td>";
		        trNew += "<td style=\"width:28px;\">" + deleteRow + "</td>";

		        trNew = trNew + " </tr>";

		        $("#ProductTable tbody").append(trNew);

		    });

		    $('#ProductTable').delegate('a.delete-photo-row', 'click', function () {
		        $(this).parent().parent().fadeOut('slow').remove();
		        return false;
		    });

        });



        var items = [];
        for( i=1; i<=24; i++ ) {
        	items[i] = document.getElementById("tabField"+i);
        }

		if(items[1]) {
			items[1].style.display = 'block';
			items[2].style.display = 'block';
			items[3].style.display = 'block';
			items[4].style.display = 'none';

			items[5].style.display = 'block';
			items[6].style.display = 'block';
			items[7].style.display = 'block';
			items[8].style.display = 'none';

			items[9].style.display = 'block';
			items[10].style.display = 'block';
			items[11].style.display = 'block';
			items[12].style.display = 'none';

			items[13].style.display = 'block';
			items[14].style.display = 'block';
			items[15].style.display = 'block';
			items[16].style.display = 'none';

			items[17].style.display = 'block';
			items[18].style.display = 'block';
			items[19].style.display = 'block';
			items[20].style.display = 'none';

			items[21].style.display = 'block';
			items[22].style.display = 'block';
			items[23].style.display = 'block';
			items[24].style.display = 'none';
		}

		function funcTab1(elem) {
			if(!items[1]) { return; }
			var txt = elem.value;
			if(txt == 'Image Advertisement') {
				items[1].style.display = 'block';
		       	items[2].style.display = 'block';
		       	items[3].style.display = 'block';
		       	items[4].style.display = 'none';
			} 
			if(txt == 'Adsense Code') {
				items[1].style.display = 'none';
		       	items[2].style.display = 'none';
		       	items[3].style.display = 'none';
		       	items[4].style.display = 'block';
			}
		};

		function funcTab2(elem) {
			if(!items[1]) { return; }
			var txt = elem.value;
			if(txt == 'Image Advertisement') {
				items[5].style.display = 'block';
		       	items[6].style.display = 'block';
		       	items[7].style.display = 'block';
		       	items[8].style.display = 'none';
			} 
			if(txt == 'Adsense Code') {
				items[5].style.display = 'none';
		       	items[6].style.display = 'none';
		       	items[7].style.display = 'none';
		       	items[8].style.display = 'block';
			}
		};

		function funcTab3(elem) {
			if(!items[1]) { return; }
			var txt = elem.value;
			if(txt == 'Image Advertisement') {
				items[9].style.display = 'block';
		       	items[10].style.display = 'block';
		       	items[11].style.display = 'block';
		       	items[12].style.display = 'none';
			} 
			if(txt == 'Adsense Code') {
				items[9].style.display = 'none';
		       	items[10].style.display = 'none';
		       	items[11].style.display = 'none';
		       	items[12].style.display = 'block';
			}
		};

		function funcTab4(elem) {
			var txt = elem.value;
			if(txt == 'Image Advertisement') {
				items[13].style.display = 'block';
		       	items[14].style.display = 'block';
		       	items[15].style.display = 'block';
		       	items[16].style.display = 'none';
			} 
			if(txt == 'Adsense Code') {
				items[13].style.display = 'none';
		       	items[14].style.display = 'none';
		       	items[15].style.display = 'none';
		       	items[16].style.display = 'block';
			}
		};

		function funcTab5(elem) {
			var txt = elem.value;
			if(txt == 'Image Advertisement') {
				items[17].style.display = 'block';
		       	items[18].style.display = 'block';
		       	items[19].style.display = 'block';
		       	items[20].style.display = 'none';
			} 
			if(txt == 'Adsense Code') {
				items[17].style.display = 'none';
		       	items[18].style.display = 'none';
		       	items[19].style.display = 'none';
		       	items[20].style.display = 'block';
			}
		};

		function funcTab6(elem) {
			var txt = elem.value;
			if(txt == 'Image Advertisement') {
				items[21].style.display = 'block';
		       	items[22].style.display = 'block';
		       	items[23].style.display = 'block';
		       	items[24].style.display = 'none';
			} 
			if(txt == 'Adsense Code') {
				items[21].style.display = 'none';
		       	items[22].style.display = 'none';
		       	items[23].style.display = 'none';
		       	items[24].style.display = 'block';
			}
		};



        
    </script>

</body>
</html>
