//constructor
function crosslinker_Class()
	{
		this.HideContent = function (d)
			{
				document.getElementById(d).style.display = "none";
			}

		this.ShowContent = function (d)
			{
				document.getElementById(d).style.display = "block";
			}

		this.ReverseContentDisplay = function (d)
			{
				if(document.getElementById(d).style.display == "block")
					{
						document.getElementById(d).style.display = "none";
					}
						else
							{
								document.getElementById(d).style.display = "block";
							}
			}

		this.confirmSubmit = function (i)
			{
				var agree=confirm('Really delete ' + i + '?');
				if (agree)
					return true ;
						else
							return false ;
			}

		this.setCookie = function (c_name,value,expiredays)
			{
				var exdate=new Date();
				exdate.setDate(exdate.getDate()+expiredays);
				document.cookie=c_name+ "=" +escape(value)+
				((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
			}

		this.getCookie = function (c_name)
			{
				if (document.cookie.length>0)
					{
						c_start=document.cookie.indexOf(c_name + "=");
						if (c_start!=-1)
							{
								c_start=c_start + c_name.length+1; 
								c_end=document.cookie.indexOf(";",c_start);
								if (c_end==-1) c_end=document.cookie.length;
									return unescape(document.cookie.substring(c_start,c_end));
							}
					}
				return "";
			}

		this.make_cookie = function (c_n)

			{
				this_cookie = this.getCookie(c_n) + '';
				value       = 1;
				z_value     = 0;
				expiredays  = 365;
				if((this_cookie==null)||(this_cookie==''))
					this.setCookie(c_n,value,expiredays);
				if(this_cookie==z_value)
					{
						this.setCookie(c_n,z_value,(-expiredays));
						this.setCookie(c_n,value,expiredays);
					}
						else
							{
								this.setCookie(c_n,value,(-expiredays));
								this.setCookie(c_n,z_value,expiredays);
							}
			}

		this.checkByParent = function (aId, aChecked)
			{
						if (document.getElementById(aId))
							{
								var collection = document.getElementById(aId).getElementsByTagName('INPUT');
								for (var x=0; x<collection.length; x++)
									{
										if (collection[x].type.toUpperCase()=='CHECKBOX')
											collection[x].checked = aChecked;
									}
							}
			}

		this.checkthebox = function ()
			{
				if ( document.formsetting.limitlinking.value == '0' )
					document.formsetting.limitlinkings.checked = false;
						else
							document.formsetting.limitlinkings.checked = true;
			}
		this.checktheboxadd = function ()
			{
				if ( document.formsetting.limitlinking.value == '0' )
					{
						alert('Please, select any number firstly! This checkbox is actually completely automated.');
						document.formsetting.limitlinkings.checked = false;
					}
						else
							{
								alert('Please, select the UNLIMITED option firstly! This checkbox is actually completely automated.');
								document.formsetting.limitlinkings.checked = true;
							}
			}

		this.check_pass_to_form = function (inp)
			{
				var checkboxid = 'checkbox_' + inp;
				if(!(document.getElementById(checkboxid).checked))
					{
						checkboxes = checkboxes.replace(' ' + inp + ' ','');
		//				alert(checkboxes);
					}
						else
							{
								checkboxes = checkboxes + ' ' + inp + ' ';
		//						alert(checkboxes);
							}
				return true;
			}

		this.pass_vars_to_nextpage = function (inp,el1)
			{
				document.getElementById(inp).innerHTML = '<input type="hidden" name="bulkconfirm_them" value="' + checkboxes + '" />';
				if(document.getElementById(el1).checked==true)
					return true;
						else
							{
								alert('Confirmation checkbox is not ticked, please do so!');
								return false;
							}
			}
	}
var crosslinkerObj = new crosslinker_Class(); //new object instance

var checkboxes = '';

crosslinkerObj.checkByParent('tbl_links', true);
