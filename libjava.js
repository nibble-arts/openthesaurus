// display confirm window
function get_confirm(text,link)
{
  Check = confirm(text);
  if (Check == true)
  {
    location.href=link;
  }
}


function goto(path)
{
  document.write(path);
}


function set_focus()
{
  self.focus();
  document.descriptor.name.focus();
}

// open rights of field in new window
function edit(field)
{
  return window.open('editright.php?field=' + field,'editright','width=400,height=250,toolbar=no,location=0,directories=0,status=no,menubar=no,scrollbars=0,resizable=0');
}

// open search result in new window
function open_search_result()
{
}

// keeps window on top
function ontop()
{
  self.focus();
  window.setTimeout("ontop()",100);
}