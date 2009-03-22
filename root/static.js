/**
*
* @package Ajax Shoutbox
* @version $Id: static.js 278 2008-04-13 08:42:03Z paul $
* @copyright (c) 2007 Paul Sohier
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
var div, hin, huit, hin2, hsmilies, hinfo, hdelete, hnr;
var config = new Array();
var post_info = timer_in = last = null;
var display_shoutbox = false;
var start = first = true;
var smilies = false;
var count = 0;
var form_name = 'chat_form';
var text_name = 'chat_message';
var bbcode = new Array();
var bbtags = new Array('[b]','[/b]','[i]','[/i]','[u]','[/u]', '[img]','[/img]', '[url]', '[/url]');
var one_open = false;
var lang = new Array();
var edit_button, edit_form = null;
var error_number = 0;

function err_msg(title, not_reload_complete)
{
	var err = new Error(title);

	if (!err.message)
	{
		err.message = title;
	}

	if (!not_reload_complete)
	{
		load_shout()
	}

	err.name = "E_USER_ERROR";// Php error?!? :D
	return err;
}

function handle(e)
{
	switch (e.name)
	{
		//Is it our error? :)
		case "E_USER_ERROR":
		case "E_CORE_ERROR":
				message(e.message, true);
			return;
		break;

		default:
		{

			tmp = lang['JS_ERR'];
			tmp += e.message;
			if (e.lineNumber)
			{
				tmp += '\n' + lang['LINE'] + ': ';
				tmp += e.lineNumber;
			}
			if (e.fileName)
			{
				tmp += '\n' + lang['FILE'] + ' : ';
				tmp += e.fileName;
			}
			message(tmp, true);
			return;
		}
	}
}

function parse_xml_to_html(xml)
{
	try
	{
		if (xml.childNodes.length == 0)
		{
			return tn('');
		}
		else if (xml.childNodes.length == 1 && xml.childNodes[0].nodeValue != null)
		{
			// With a tag in it, its bigger as 1?

			return tn(xml.childNodes[0].nodeValue);
		}
		else
		{
			var div = ce('span');
			loop:

			for (var i = 0; i < xml.childNodes.length; i++)
			{

				switch (xml.childNodes[i].nodeType)
				{
					case 3:
						div.appendChild(document.createTextNode(xml.childNodes[i].nodeValue));
					break;

					case 9:
					case 8:
					case 10:
					case 11:
						// continue;
					break;

					case 1:
						if (xml.childNodes[i].childNodes.length == 0 && xml.childNodes[i].nodeName != 'br' && xml.childNodes[i].nodeName != 'img' && xml.childNodes[i].nodeName != 'hr')
						{
							break;
						}

						// This is a difficult one :)
						switch (xml.childNodes[i].nodeName)
						{
							case 'blockquote':
								var q = ce('blockquote');
								q.className = 'quote';
								q.appendChild(parse_xml_to_html(xml.childNodes[i]));
								add_style(xml.childNodes[i], q);
								div.appendChild(q);
							break;

							case 'a':
								var a = ce('a');

								a.href = xml.childNodes[i].getAttribute('href');
								a.appendChild(parse_xml_to_html(xml.childNodes[i]));

								add_style(xml.childNodes[i], a);

								div.appendChild(a);
							break;

							case 'img':
								var img = ce('img');

								img.alt = xml.childNodes[i].getAttribute('alt');
								img.src = xml.childNodes[i].getAttribute('src');
								img.border = 0;
								add_style(xml.childNodes[i], img);

								div.appendChild(img);
							break;

							case 'script':
								// Bad boy, die.
								return;
							break;

							default:
							{
								try
								{
									var e = ce(xml.childNodes[i].nodeName);
								}
								catch (e)
								{
									break;
								}
								e.appendChild(parse_xml_to_html(xml.childNodes[i]));

								add_style(xml.childNodes[i], e);
								div.appendChild(e)
							}
						}
					break;
				}
			}
		}
		return div;
	}
	catch (e)
	{
		handle(e);
		return div;
	}
}
function add_style(element, html)
{
	var Class = element.getAttribute('class');

	if (Class != null)
	{
		html.className = Class;
	}

	var styles = element.getAttribute('style');

	if (styles == null)
	{
		return;
	}
	if (styles.indexOf(';') == -1)
	{
		styles += ';';
	}
	styles = styles.split(';');
	for (var j = 0; j < styles.length; j++)
	{
		var style = styles[j].split(':');

		if (style[0])
		{
			style[0] = trim(style[0]);
		}

		if (style[1])
		{
			style[1] = trim(style[1]);
		}

		switch (style[0])
		{
			case 'font-style':
				html.style.fontStyle = style[1];
			break;

			case 'font-weight':
				html.style.fontWeight = style[1];
			break;

			case 'font-size':
				try
				{
					html.style.fontSize = style[1];
				}
				catch (e){}
			break;

			case 'line-height':
				html.style.lineHeigt = style[1];
			break;

			case 'color':
				html.style.color = style[1];
			break;

			case 'text-decoration':
				html.style.textDecoration = style[1];
			break;
		}
	}
}

function trim(value)
{
	value = value.replace(/^\s+/,'');
	value = value.replace(/\s+$/,'');
	return value;
}
function http()
{
	try
	{
		var http_request = false;
		if (window.XMLHttpRequest)
		{
			// Mozilla, Safari,...
			http_request = new XMLHttpRequest();

			if (http_request.overrideMimeType)
			{
				http_request.overrideMimeType('text/xml');
			}
		}
		else if (window.ActiveXObject)
		{ // IE
			try
			{
				http_request = new ActiveXObject('Msxml2.XMLHTTP');
			}
			catch (e)
			{
				try
				{
					http_request = new ActiveXObject('Microsoft.XMLHTTP');
				}
				catch (e)
				{
				}
			}
		}

		if (!http_request)
		{
			 throw err_msg(lang['no_ajax']);
		}
		return http_request;

	}
	catch (e)
	{
		handle(e);
		return false;
	}
}

function message(msg, color, no_reload)
{
	try
	{
		if (document.getElementById('msg_txt') != null)
		{
			document.getElementById('msg_txt').innerHTML = '';
			var tmp = ce('p');
			tmp.appendChild(tn(msg));
			if (color)
			{
				tmp.style.color = 'red';
			}
			document.getElementById('msg_txt').appendChild(tmp);
		}
		else
		{
			div.innerHTML = '';
			var tmp = ce('p');
			tmp.appendChild(tn(msg));
			if (color)
			{
				tmp.style.color = 'red';
			}
			div.appendChild(tmp);
		}

		// We reload everything after 5 seconds when an error happens, to prevent errors that are happening, and
		// the shoutbox dont work anymore without a reload.
		if (!no_reload)
		{
			last = 0;
			one_open = false;
			hin = http(); // Reset HTTP data.
			if (error_number <= 10)
			{
				timer_in = setTimeout('reload_post();',5000);
			}
			else
			{
				clearTimeout(timer_in);
				return;
			}
			error_number++;
		}
	}
	catch (e)
	{
		handle(e);
		return false;
	}
}

function cp()
{
	var sep = ce('span');
	sep.className = 'page-sep';
	sep.appendChild(tn(lang['COMMA_SEPARATOR']));
	return sep;
}

function bbcode_buttons(posting_box)
{
	// BBcode buttons ;)
	var bbcode = ce('input');
	bbcode.type = 'button';
	bbcode.className = 'button2 btnmain';
	bbcode.accesskey = 'b';
	bbcode.name = bbcode.id = 'addbbcode0';
	bbcode.value = bbcode.defaultValue = ' B ';
	bbcode.style.fontWeight = 'bold';
	bbcode.style.width = '33px';
	bbcode.onclick = function()
	{
		var tfn = form_name;
		form_name = 'chat_form';
		var ttn = text_name;
		text_name = 'chat_message';
		bbstyle(0);
		form_name = tfn;
		text_name = ttn;
	}

	posting_box.appendChild(bbcode);

	posting_box.appendChild(tn(' '));

	var bbcode = ce('input');
	bbcode.type = 'button';
	bbcode.className = 'button2 btnmain';
	bbcode.accesskey = 'i';
	bbcode.name = bbcode.id = 'addbbcode2';
	bbcode.value = bbcode.defaultValue = ' I ';
	bbcode.style.fontStyle = 'italic';
	bbcode.style.width = '33px';
	bbcode.onclick = function()
	{
		var tfn = form_name;
		form_name = 'chat_form';
		var ttn = text_name;
		text_name = 'chat_message';
		bbstyle(2);
		form_name = tfn;
		text_name = ttn;
	}

	posting_box.appendChild(bbcode);

	posting_box.appendChild(tn(' '));

	var bbcode = ce('input');
	bbcode.type = 'button';
	bbcode.className = 'button2 btnmain';
	bbcode.accesskey = 'u';
	bbcode.name = bbcode.id = 'addbbcode4';
	bbcode.value = bbcode.defaultValue = 'U';
	bbcode.style.textDecoration = 'underline';
	bbcode.style.width = '33px';
	bbcode.onclick = function()
	{
		var tfn = form_name;
		form_name = 'chat_form';
		var ttn = text_name;
		text_name = 'chat_message';
		bbstyle(4);
		form_name = tfn;
		text_name = ttn;
	}

	posting_box.appendChild(bbcode);

	posting_box.appendChild(tn(' '));

	var bbcode = ce('input');
	bbcode.type = 'button';
	bbcode.className = 'button2 btnmain';
	bbcode.accesskey = 'p';
	bbcode.name = bbcode.id = 'addbbcode6';
	bbcode.value = bbcode.defaultValue = ' IMG ';
	bbcode.style.width = '43px';
	bbcode.onclick = function()
	{
		var tfn = form_name;
		form_name = 'chat_form';
		var ttn = text_name;
		text_name = 'chat_message';
		bbstyle(6);
		form_name = tfn;
		text_name = ttn;
	}

	posting_box.appendChild(bbcode);

	posting_box.appendChild(tn(' '));

	var bbcode = ce('input');
	bbcode.type = 'button';
	bbcode.className = 'button2 btnmain';
	bbcode.accesskey = 'w';
	bbcode.name = bbcode.id = 'addbbcode8';
	bbcode.value = bbcode.defaultValue = ' URL ';
	bbcode.style.width = '43px';
	bbcode.onclick = function()
	{
		var tfn = form_name;
		form_name = 'chat_form';
		var ttn = text_name;
		text_name = 'chat_message';
		bbstyle(8);
		form_name = tfn;
		text_name = ttn;
	}
	posting_box.appendChild(bbcode);
}

function delete_message(post_id)
{
	if (hdelete.readyState == 4 || hdelete.readyState == 0)
	{
		// Lets got some nice things :D
		hdelete.open('GET',delete_url + '&id=' + post_id + '&rand='+Math.floor(Math.random() * 1000000),true);

		hdelete.onreadystatechange = function()
		{
			try
			{
				if (hdelete.readyState != 4)
				{
					return;
				}
				if (hdelete.readyState == 4)
				{
					xml = hdelete.responseXML;

					if (typeof xml != 'object')
					{
						throw err_msg(lang['SERVER_ERR']);
						return;
					}

					if (xml.getElementsByTagName('error') && xml.getElementsByTagName('error').length != 0)
					{
						err = xml.getElementsByTagName('error')[0].childNodes[0].nodeValue;
						message(err, true);
						return;
					}
					else
					{
						message(lang['MSG_DEL_DONE']);
					}
					setTimeout("document.getElementById('msg_txt').innerHTML = ''",3000);

					last = 0;// Reset last, because if we delete the last message, the next messages cannot load correctly.
					clearTimeout(timer_in);
					reload_post();
					reload_page();
				}
			}
			catch (e)
			{
				handle(e);
				return;
			}
		}

		hdelete.send(null);
	}
}

function handle_edit(dd, inh, i)
{
	msg3 = ce('span');
	msg3.id = 'text' + i;
	msg3.style.display = 'none';

	dd.appendChild(msg3);

	// User can edit this shout.
	edit_form = ce('form');
	edit_form.id = 'form' + i;
	edit_form.i = i;
	edit_form.style.display = 'none';
	edit_form.onsubmit = function()
	{
		return false
	}

	var input = ce('input');
	input.id = 'input' + i;
	input.i = i;
	input.value = input.defaultValue = inh.getElementsByTagName('msg_plain')[0].childNodes[0].nodeValue;
	input.style.width = '125px';

	input.onkeypress = function(evt)
	{
		try
		{
			evt = (evt) ? evt : event;
			var c = (evt.wich) ? evt.wich : evt.keyCode;
			if (c == 13)
			{
				document.getElementById('submit' + this.i).click();
				evt.returnValue = false;
				this.returnValue = false;
				return false;
			}
			return true;
		}
		catch (e)
		{
			handle(e);
			return;
		}
	}

	edit_form.appendChild(input);

	var input = ce('input');
	input.type = 'button';
	input.id = 'submit' + i;
	input.value = lang['EDIT'];
	input.i = i;

	input.shout_id = inh.getElementsByTagName('shout_id')[0].childNodes[0].nodeValue;

	input.onclick = function()
	{
		//one_open = false;

		i = this.i;

		document.getElementById('form' + i).style.display = 'none';
		//

		document.getElementById('text' + i).style.display = 'block';
		document.getElementById('text' + i).innerHTML = '';
		document.getElementById('text' + i).appendChild(tn(lang['SENDING_EDIT']));

		var hedit = http();

		if (hedit.readyState == 4 || hedit.readyState == 0)
		{
			hedit.open('POST', edit_url + '&last=' + last + '&rand='+Math.floor(Math.random() * 1000000),true);
			hedit.i = i;
			hedit.onreadystatechange = function()
			{
				try
				{
					if (hedit.readyState != 4)
					{
						return;
					}
					i = hedit.i;

					one_open = false;

					document.getElementById('text' + i).style.display = 'none';
					document.getElementById('shout' + i).style.display = 'block';
					document.getElementById('edit_button' + i).style.display = 'inline';

					setTimeout("document.getElementById('msg_txt').innerHTML = ''", 3000);
					last = 0;
					reload_post();

					try
					{
						document.getElementById("post_message").style.display = "block";
					}
					catch(e){}

					var xml = hedit.responseXML;

					if (typeof xml != 'object')
					{
						throw err_msg(lang['SERVER_ERR']);
						return;
					}

					if (xml.getElementsByTagName('error') && xml.getElementsByTagName('error').length != 0)
					{
						var msg = xml.getElementsByTagName('error')[0].childNodes[0].nodeValue;
						throw err_msg(msg, true);
						return;
					}
					else
					{
						message(lang['EDIT_DONE']);
					}
				}
				catch (e)
				{
					handle(e);
					return;
				}
			}

			post = 'chat_message=';

			post += encodeURIComponent(document.getElementById('input' + i).value);

			post += '&shout_id=' + this.shout_id;

			document.getElementById('input' + i).value = '';

			hedit.setRequestHeader('Content-Type','application/x-www-form-urlencoded');

			hedit.send(post);
		}
	}

	edit_form.appendChild(input);

	var input = ce('input');
	input.type = 'button';
	input.value = lang['CANCEL'];
	input.i = i;

	input.onclick = function()
	{
		one_open = false;

		try
		{
			document.getElementById("post_message").style.display = "block";
		}
		catch(e){}

		i = this.i;
		document.getElementById('form' + i).style.display = 'none';
		document.getElementById('shout' + i).style.display = 'block';
		document.getElementById('edit_button' + i).style.display = 'inline';
	}

	edit_form.appendChild(input);

	edit_button.style.display = 'inline';
	edit_button.i = i;
	edit_button.id = 'edit_button' + i;

	edit_button.onclick = function()
	{
		if (one_open)
		{
			alert(lang['ONLY_ONE_OPEN']);
			return;
		}
		one_open = true;

		try
		{
			document.getElementById("post_message").style.display = "none";
		}
		catch(e){}

		i = this.i;
		document.getElementById('form' + i).style.display = 'block';
		document.getElementById('shout' + i).style.display = 'none';
		document.getElementById('edit_button' + i).style.display = 'none';
	}
	return dd;
}

/**
 * Lazyness ftw
 *
 */
function ce(e)
{
	return document.createElement(e);
}
function tn(e)
{
	return document.createTextNode(e);
}