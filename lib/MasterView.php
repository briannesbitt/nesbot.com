<?
class MasterView extends Slim_View {
   protected $app;
   protected $env;
   protected $masterTemplate;

   public function __construct($app, $masterTemplate = 'template.php') {
      parent::__construct();
      $this->app = $app;
      $this->masterTemplate = $masterTemplate;
      $this->env = $app->environment();
   }

   public function setMasterTemplate($masterTemplate) {
      $this->masterTemplate = $masterTemplate;
   }

   public function render($template) {
      $this->setData('childView', $template);
      $this->injectDefaultVariables();
      return parent::render($this->masterTemplate);
   }

   public function partial($template, $data = []) {
      $this->injectDefaultVariables($data);
      extract($data);
      require $this->getTemplatesDirectory().'/'.$template;
   }

   private function injectDefaultVariables(&$data = null) {
      if (is_array($data)) {
         $data['urlBase'] = $this->urlBase();
         $data['urlImg'] = $this->urlImg();
         $data['urlFullImg'] = $this->urlFullImg();
         $data['urlCss'] = $this->urlCss();
         $data['urlJs'] = $this->urlJs();
         $data['isLive'] = $this->isLive();
      }
      else {
         $this->setData('urlBase', $this->urlBase());
         $this->setData('urlImg', $this->urlImg());
         $this->setData('urlCss', $this->urlCss());
         $this->setData('urlJs', $this->urlJs());
         $this->setData('isLive', $this->isLive());
      }
   }

   public function urlBase() {
      return $this->env['URLBASE'];
   }
   public function urlImg() {
      return $this->env['URLIMG'];
   }
   public function urlFullImg() {
      return $this->env['URLFULLIMG'];
   }
   public function urlCss() {
      return $this->env['URLCSS'];
   }
   public function urlJs() {
      return $this->env['URLJS'];
   }

   public function isLive() {
      return $this->app->getMode() == 'live' || $this->app->getMode() == 'production';
   }
}