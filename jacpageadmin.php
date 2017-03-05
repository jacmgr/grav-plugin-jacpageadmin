<?php
namespace Grav\Plugin;
use Grav\Common\Plugin;
use Grav\Common\Page\Page;
use Grav\Common\Page\Pages;

// use Grav\Common\Utils;
// use Grav\Common\Uri;
use Grav\Plugin\Form;
use RocketTheme\Toolbox\Event\Event;
//use RocketTheme\Toolbox\Session\Message;

class JacpageadminPlugin extends Plugin
{
    /**
     * Subscribe to grav events
     * @return array
     */
    public static function getSubscribedEvents()
    {
        // initialize when plugins are ready
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }
    /**
     * Initialize configuration
     */
    public function onPluginsInitialized()
    {
        // Don't load in Admin-Backend
        if ($this->isAdmin()) {
            $this->active = false;
            return;
        }
        //load up the plugin configs
        $this->jacadminConfig = $this->config->get('plugins.jacpageadmin');

        // if the uri is a pagaeadmin route, possibly process
        $this->pageadmin_action = null;
        if ($this->grav['uri']->path() == $this->jacadminConfig['route_edit']) {
          $this->pageadmin_action = 'edit';
        }
        if ($this->grav['uri']->path() == $this->jacadminConfig['route_create']) {
         $this->pageadmin_action = 'create';
        } 
        if ($this->grav['uri']->path() == $this->jacadminConfig['route_source']) {
         $this->pageadmin_action = 'source'; 
        } 
        if ($this->grav['uri']->path() == $this->jacadminConfig['route_home']) {
         $this->pageadmin_action = 'home';
        }
        //if url is not pageadmin function
        if (!$this->pageadmin_action) {
            $this->active = false;
            return;
        }
        // if not logged in only allow action source.......and we are logged in all ok.
        // if you want to require login on source then change code here....
        // if any other action and not logged in, don't activate this plugin and return......
        // Check on logged in user and authorization for page editing
        // "site.editor" is in the users account page.
        $this->active = true;
        $user = $this->grav['user'];
        if (!$user->authenticated && !$user->authorize("site.editor") && ($this->pageadmin_action != 'source')) {
            $this->active = false;
            return;
        }
        // Put a message in debug bar
        $this->grav['debugger']->addMessage("[jacpageadmin]: ACTIVE IS TRUE");
        //Add the events to listen.
        //if post is different than display.
        if(empty($_POST)){ //no post
            $this->enable([
                'onPagesInitialized' => ['onPagesInitialized', 0],
                'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
                'onOutputGenerated'   => ['onOutputGenerated', 0],
            ]);          
        } else {  //got a post
            $this->enable([
                'onPagesInitialized' => ['onPagesInitialized', 0],
                'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
                'onFormProcessed' => ['onFormProcessed', 0],
                'onFormValidationProcessed' => ['onFormValidationProcessed', 0]
            ]);          
        }
    }
    /**
     * Add twig paths to plugin templates.
     * I don't have any templates yet, but will soon, so..this is the function.
     * in future to use, add it to the listener array in onPluginsInitialized()
     */
    public function onTwigTemplatePaths()
    {
        $twig = $this->grav['twig'];
        $twig->twig_paths[] = __DIR__ . '/templates';
    }
    /**
     * Grav Pages all loaded
     * Add the jacadmin page requested in the action.
     */
    public function onPagesInitialized()
    {  
        $this->route = $this->grav['uri']->path();
        //only adds the specific page: edit, create, etc....
        $this->addPageadminPages();  
    }
    /*
    *  This is fired BEFORE onFormProcessed is fired.
    *  If there is any data problems, flag message like below and it will return to the
    *  form page.
    */
    public function onFormValidationProcessed(Event $event)
    {
        $config = $this->grav['config'];
        $form = $event['form'];
        $uri = $this->grav['uri']->url;
        $session = $this->grav['session'];

        //If have way to validate.....Do it here....
        //maybe check if filename is still good....not changed...etc....
        //NO CHECKS YET.
        $validationerrors = false;
        
        //if errors identified above, return to form....
        if ($validationerrors){     
              $this->grav->fireEvent('onFormValidationError', new Event([
                'form'    => $form,
                'message' => "From validator event: Something wrong with your input. Fix it!"
                ]));
            $event->stopPropagation();
            return; 
        }  
    }
    /**
     * If FORM is POSTED; then these are the grav "process" actions.
     */               
    public function onFormProcessed(Event $event)
    {
        $this->form = $event['form'];
        $action = $event['action'];
        $params = $event['params'];   //no idea what this is: value is 1
        $uri = $this->grav['uri'];
        
        $post = !empty($_POST) ? $_POST : [];
        
        switch ($action) {
            case 'jacpageadmin_source':
                  $event->stopPropagation();
                  $this->grav->redirect($post['data']['pagetoedit']);
                  exit; 
            case 'jacpageadmin_edit':
                  $event->stopPropagation();
                  //load the original page we were editting pagetoedit; saved on hidden field in form
                  $page = $this->grav['pages']->dispatch($post['data']['pagetoedit']); //from hidden field
                  //the standard grav folder page type filename
                  $filename = $page->path().'/'.$page->name();   //standard grav folder page
                  // start jaccms ---------------------------------------------------------------------
                  //if using jaccms MultiFileFolder plugin is being used for this page then actual name is different 
                  if(isset($page->jaccms)){ 
                    $filename = $page->path().'.md'; //MultiFileFolder jaccms location
                  }
                  // end jaccms ---------------------------------------------------------------------
                  //don't know how to seperate and save old headers with new content. So only expert mode supported.
                  if(!$this->jacadminConfig['expertmode']) { echo 'jacpageadmin: only expert mode is supported.'; exit; }
                  $this->jacadmin_writefilecontents($filename, $post['data']['pagetext']);
                  //no error checks...yet...redirect back to the rendered page
                  $this->grav->redirect($post['data']['pagetoedit']);
                  exit; 
            case 'jacpageadmin_create':
                  $event->stopPropagation();
                  
                  //don't know how to deal with any yaml to create new headers seperately.
                  //So I only create a new file with a title frontmatter. 
                  // Add the newpagecontent with frontmatter title
                  $newpagecontent = "---\n";
                  $newpagecontent .= "title: ".$post['data']['title']."\n";
                  $newpagecontent .= "---\n";
                  $newpagecontent .= "Add your content here....\n";

                  // Create a new page in a location based on the page you were on when you clicked create. (referrer)
                  $currentpage = $this->grav['pages']->dispatch($post['data']['referrer']); //from hidden field
                  //standard grav folder page
                  //$post['data']['referrer'];
                  $currentroute = $currentpage->route();
                  $currentfolder = $currentpage->path();
                  $currentfilename = $currentpage->name();  //usually item.md??
                  $currentparent = $currentpage->parent();
                  //C:/xampp56/htdocs/grav/pinpress/pin01/user/pages/01.pinpressblog/papilion-minter-savior/item.md
                  $newpagefolder = dirname($currentfolder). '/'. $post['data']['pageslug'];
                  $newpageroute = dirname($currentroute). '/'. $post['data']['pageslug'];
                  //for standard grav this is usually "item.md" or whatever the current page file name was.
                  $newpagefilename = $currentfilename;  
                        // start jaccms ---------------------------------------------------------------------
                        //if the current page is using jaccms MultiFileFolder plugin  
                        //The sibling folder already exists since jaccms has all files in same folder.
                        if(isset($currentpage->jaccms)) {
                            $newpagefolder = dirname($currentfolder);
                            $newpagefilename = $post['data']['pageslug'].'.md';
                        }
                        // end jaccms -------------------------------------------------------------------------
                  //check if folder exists and if not create it..(grav pages.)
                  //No error checking
                  $filename = $newpagefolder.'/'.$newpagefilename;
                  //if not exists folder; create it.
                  if (!file_exists($newpagefolder)) {
                      mkdir($newpagefolder, 0777, true);
                  }
                  //wite the new content file.
                  //no error checking.
                  if(!is_file($filename)){
                      //Save our content to the file.
                      file_put_contents($filename, $newpagecontent);
                      //redirect to the page
                      $redirectto = $this->jacadminConfig['route_edit'].'?p='.$newpageroute; 
                      $this->grav->redirect($redirectto);
                      exit;
                  }else
                  {
                      echo 'caccelled: file already exists.  Put error check in correct place!'; 
                      exit;
                  }        
            case 'jacpageadmin_delete':
                  $event->stopPropagation();
                      $redirectto = $this->jacadminConfig['route_home'];
                      $this->grav->redirect($redirectto);
                  exit; 
            case 'jacpageadmin_home':    //not really an event.  
                      $redirectto = $this->jacadminConfig['route_home'];
                      $this->grav->redirect($redirectto);
                  exit; 
        }
    }  
    /**
     * Add twig Variables for templates.
     */
    public function onTwigSiteVariables()
    {
        $twig = $this->grav['twig'];
        //can be used for links in the form page content area
        $twig->twig_vars['querystring'] = '?p='.$this->grav['uri']->query('p');
        $twig->twig_vars['pageedit'] = $this->grav['pages']->dispatch($this->grav['uri']->query('p')); 
        
        // The simplemde editor for edit page.       
        // CSS and JS Assets
        $assets = $this->grav['assets'];
        // Add jQuery library
        $assets->add('jquery', 101);

        // Add SimpleMDE Markdown Editor
        $assets->addCss('//cdn.jsdelivr.net/simplemde/latest/simplemde.min.css', 1);
        $assets->addJs('//cdn.jsdelivr.net/simplemde/latest/simplemde.min.js', 1);

        // Load SimpleMDE inline Javascript code from configuration file
        $assets->addInlineJs(file_get_contents('plugin://jacpageadmin/assets/js/simplemde_config.js'), 1);
    }
    /**
     * Final mash before html is sent to screen
     * Replace the form field default value placeholders with actual defaults.
     */    
    public function onOutputGenerated()
    {
        //I don't know how to set defaults in Frontend Form.
        //This is my workaround using dummy text in the form page 'ByPlugin.somefiledname'
        //get the default values for form fields
        $jacadminFormData = $this->jacadmin_FormDefaultValues();
        
        //replace default value placeholders
        foreach ($jacadminFormData as $field => $value) {
          $this->grav->output = str_replace('ByPlugin.'.$field, $value, $this->grav->output);  
        }
    }
    /* ************************************************************* */
    /* Local Functions
    /* ************************************************************* */
    /**
     * Prepare the default values for the forms.
     * Including the content to edit field.
     */ 
    public function jacadmin_FormDefaultValues()
    {
        //Page name for form data is in url
        //i.e. http://localhost/pageadmin/source?p=/blog/cheonggyesa-temple
        //NOTE: THIS IS VERY CUSTOMIZED TO THE SPECIFIC FORMS in this PLUGIN
        $uri = $this->grav['uri'];
        //todo: if not query then error...... or default to create?
        $pages = $this->grav['pages'];               //all page objects
        $page = $pages->dispatch($uri->query('p'));  //the pageroute from the p=route
        //just in case......
        if (!$page) { 
          echo 'do proper error message: jacadmin_FormDefaultValues:: ouch...cant find page or you didn`t give a page: '.$uri->query('p');
          exit;
        }
        //Form hidden fields to support processing after post      
        $JPAdata['plugin_action'] = $this->pageadmin_action;
        $JPAdata['referrer'] = $uri->referrer();   //the page you were on when you clicked edit/create/source
        $JPAdata['pagetoedit'] = $uri->query('p'); //the page you want edited
        //Form READONLY Fields in page EDIT form file, but active on CREATE
        $JPAdata['pagetitle'] = $page->title();  
        $JPAdata['pageslug'] = $page->slug();  
        //The editor content text area
        if($this->jacadminConfig['expertmode']){
            //expertmode = true: if editor is using the entire file contents as expert mode use this:
            $filename = $page->path().'/'.$page->name();   //standard grav folder page
                    // start jaccms ---------------------------------------------------------------------
                    //if jaccms MultiFileFolder plugin is being used for this page 
                    if(isset($page->jaccms)){
                       $filename = $page->path().'.md'; //MultiFileFolder jaccms location
                    }
                    // end jaccms ---------------------------------------------------------------------
            $JPAdata['pagetext'] = $this->jacadmin_getfilecontents($filename);  //Fule file with header...."expert mode" HA!
            //also a htmlentity version
            $JPAdata['htmlentitiespagetext'] = htmlentities($JPAdata['pagetext']);
        }else
        {
            //NON EXPERT MOPDE HAS NOT BEEN IMPLEMENTED YET.  SAVE/CREATE FILES DOES NOT SUPPORT THIS.
            //expertmode = false :: if editor is using content only......use this way.
            $JPAdata['pagetext'] = $page->rawMarkdown();  //This one works: ONLY CONTENT NO HEADER          
            //also a htmlentity version
            $JPAdata['htmlentitiespagetext'] = htmlentities($JPAdata['pagetext']);
        }
        
        return $JPAdata;
    }
    /**
     * Add the form pages from plugin.
     */
    public function addPageadminPages()
    {
       
        $pages = $this->grav['pages'];
        $page = $pages->dispatch($this->route);
         
        if (!$page) {
            // Then no user page pverride; The page default is in our plugin folder pages. Load it up.
            $page = new Page;
            $filename = __DIR__ . "/pages/pageadmin_".$this->pageadmin_action.".md"; // /pages/pageadmin_edit.md  etc.... 
            $page->init(new \SplFileInfo($filename));
            $page->template('form');  //just in case filename is not "form.md"
             //doesn't seem to matter which slug base I use.
            //$page->slug(basename($this->route)); 
            $page->slug($this->route);
            //when use the page out of teh plugin repository, you need to do this!  Why??
            $twig = $this->grav['twig'];
            $twig->twig_vars['form'] = $page->header()->form;
            //add the page into the pages repository
            $pages->addPage($page, $this->route);
            //override the current page (likely a 404) to be this newly added page
            // make sure page is not frozen! Sometimes you get error: Cannot override frozen service 
            unset($this->grav['page']);
            $this->grav['page'] = $page;
            //add some debug messages
            $this->grav['debugger']->addMessage("[jacpageadmin-addPageadminPages] Looking for route: ".$this->route);            
            $this->grav['debugger']->addMessage("[jacpageadmin-addPageadminPages] Page was added: ".$filename);
        }else
        {
          $this->grav['debugger']->addMessage("[jacpageadmin-addPageadminPages] Using USERS custom form: ".$this->route); 
        }
    }
    /**
     * standard php read a file.
     */
    public function jacadmin_getfilecontents($filename)
    {
      //should chek if file exists, but since already in cache, low risk for now.
      $handle = fopen($filename, 'r');
      $contents = fread($handle,filesize($filename));
      fclose($handle);
      return $contents;
    }
    /**
     * standard php write/create a file.
     */
    public function jacadmin_writefilecontents($filename, $data)
    {
      $handle = fopen($filename, 'w') or die('Cannot open file:  '.$filename);
      fwrite($handle, $data);
      fclose($handle);
    }
}
                  // ===========================================================================
                  //abandoned unused interesting tidbits....
                  // ===========================================================================
                  //echo '<pre>pagetoedit: '; print_r($this->form->value('pagetoedit')); echo '</pre>';  //works
                  //echo '<pre>allvalues: '; print_r($this->form->value()); echo '</pre>';  //NOT WORK
                  //echo '<pre>allvalues: '; print_r($this->form->value()->toArray()); echo '</pre>'; //This Works
                  
                  /*
                      //new file was created; now redirect to edit it. Get the new route first.
                      //C:/xampp56/htdocs/grav/pinpress/pin01/user/pages/02.blog/20170302-4.md
                      $fileroute = dirname($filename) .'/'.pathinfo($filename, PATHINFO_FILENAME);  //NO
                      list($notused, $newpageroute) = explode('user/pages', $fileroute);            //NO
                      //above did not work, since i don't know how to remove the folder ordering prefixes.
                      //so add the page into grave and get its route from grav
                      $page = new Page;
                      $page->init(new \SplFileInfo($filename));
                      $page->slug($post['data']['pageslug']);
                      $page->path(dirname($filename));
                      $page->parent($currentparent);    //need this to get the correct route?
                      $content_exists = true;           //need this to make it routable?
                      $this->grav['pages']->addPage($page);//add the page into the pages repository
                          
                      $redirectto = $this->jacadminConfig['route_edit'].'?p='.$page->route();
                  */