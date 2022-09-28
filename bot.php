<?php

// Класс
class TelegramBot {
	
	private $access_token = '1341942252:AAFAYCTQ1ijgGvvUQlV5W_XXXXXXXXXXX'; //  токен
	private $dbName="sergey_sh_telegrambot";
	private $dbUser="sergey_sh_bot";
	private $dbPassword="xxxxxxxxx"; // Здесь был пароль
	private $dbLocation="localhost";
	
	private $api; // API
	private $output; // Достаем данные
	private $chat_id; // CHAT ID
	private $message; // Сообщение
	private $callback_query; // CALLBACK для кнопки
	private $data;
	private $message_id;
	private $chat_id_in;
	
	// Конструктор
	function TelegramBot() {
		
		// БЛОК СОЕДИНЕНИЯ С БД
		$dbcnx = @mysql_connect($this->dbLocation,$this->dbUser,$this->dbPassword);
		if (!$dbcnx) // 
		{
			echo("<P>В настоящий момент сервер базы данных не доступен</P>");
			exit();
		} else {
			// echo "Все ОК!";
	
			if (!@mysql_select_db($this->dbName, $dbcnx)) 
			{
			echo( "<P>В настоящий момент база данных не доступна</P>" );
			exit();
			} else {
			// echo "Таблица выбрана!";
		}
	
		mysql_query("SET NAMES UTF8");
		//mysql_query("SET CHARACTER SET 'utf8';"); 
		//mysql_query("SET SESSION collation_connection = 'utf8_general_ci';"); 
	
		}
		// -----------------------
		// КОНЕЦ СОЕДИНЕНИЯ С БД
		
		
		// ИНИЦИЛИЗАЦИЯ ВАЖНЫХ ПЕРЕМЕННЫХ
		$this->api = 'https://api.telegram.org/bot' . $this->access_token; // API
		$this->output = json_decode(file_get_contents('php://input'), TRUE); // Достаем данные
		$this->chat_id = $this->output['message']['chat']['id']; // CHAT ID
		$this->message = $this->output['message']['text']; // Сообщение
		$this->callback_query = $this->output['callback_query']; // CALLBACK для кнопки
		$this->data = $this->callback_query['data'];
		$this->message_id = ['callback_query']['message']['message_id'];
		$this->chat_id_in = $this->callback_query['message']['chat']['id'];
		// ------------------------------
		// КОНЕЦ ИНИЦИАЛИЗАЦИИ
		
		// Проверяем сообщения и CallBack'и
		$this->SwitchMessage($this->message);
		$this->SwitchCallback($this->data);
		
	}
	// --------------------------
	
	// Метод проверки сообщений
	function SwitchMessage($message) {
		
		switch($message) {
    

	
		case '/test':  
		$inline_button1 = array("text"=>"Google url","url"=>"http://google.com");
		$inline_button2 = array("text"=>"Шевелись, Плотва","callback_data"=>'plotva!');
		$inline_keyboard = [[$inline_button1,$inline_button2]];
		$keyboard=array("inline_keyboard"=>$inline_keyboard);
		$replyMarkup = json_encode($keyboard); 
		$this->sendMessage($this->chat_id, "ok", $replyMarkup);
		break;
	
		case '/start':  
		$inline_button1 = array("text"=>"WIKI HELP","url"=>"https://helpsite.ru/");
		$inline_keyboard = [[$inline_button1]];
		$keyboard=array("inline_keyboard"=>$inline_keyboard);
		$replyMarkup = json_encode($keyboard); 
		$snd_msg="Добро пожаловать в Bot. Данный бот
		предназначен для быстрого решения проблем.
		Для начала работы наберите ошибку.
		Спасибо!";
		$this->SendMessage($this->chat_id, $snd_msg, $replyMarkup);
		break;
	
		case '/help':  
		$inline_button1 = array("text"=>"WIKI HELP","url"=>"https://helpsite.ru/");
		$inline_keyboard = [[$inline_button1]];
		$keyboard=array("inline_keyboard"=>$inline_keyboard);
		$replyMarkup = json_encode($keyboard); 
		$snd_msg="Раздел помощи:
		/start : стартовая информация
		<Ошибка>: Ошибка с вариантами ее решения, Например, просто наберите 'XXXX' (код ошибки, без кавычек) и отвечайте на вопросы Бота";
		$this->sendMessage($this->chat_id, $snd_msg, $replyMarkup);
		break;
	

	
		default:
		$array_sql=$this->GetDBbyTag($message);
		if ($array_sql['status']=="yes") {
		
		$send_msg=$array_sql['message'];
		$buttonText=$array_sql['buttonText'];
		$buttonType=$array_sql['buttonType'];
		$buttonCallback=$array_sql['buttonCallback'];
		
		// Если есть кнопк, то делаем их
		if (strlen($buttonType)>=10) {
			$replyMarkup=$this->NewKeyboard($buttonText,$buttonType,$buttonCallback);
		} else {		
			$replyMarkup="";
		}
		$this->SendMessage($this->chat_id, "$send_msg", $replyMarkup);
		}
	
	}
		
	}
	// -------------------------
	
	// Метод проверки CallBack'ов
	function SwitchCallback($data) {
		
		switch($data){
			
		default:
		$array_sql=$this->GetDBbyTag($data);

	
		if ($array_sql['status']=="yes") {
		
		$send_msg=$array_sql['message'];
		
		$buttonText=$array_sql['buttonText'];
		$buttonType=$array_sql['buttonType'];
		$buttonCallback=$array_sql['buttonCallback'];
		
		// Если есть кнопк, то делаем их
		if (strlen($buttonType)>=10) {
			$replyMarkup=$this->NewKeyboard($buttonText,$buttonType,$buttonCallback);
		} else {		
			$replyMarkup="";
		}
		
		$this->SendMessage($this->chat_id_in, "$send_msg", $replyMarkup);
		}
	
		break;

		}
		
	}
	// --------------------------
	
	// Метод создания новой клавиатуры
	function NewKeyboard($text,$type,$callback) {
	
	$text_array=explode(";",$text); // достаем все тексты кнопок
	$type_array=explode(";",$type); // Достаем типы кнопок
	$callback_array=explode(";",$callback); // Достаем CallBack'и
	
	$inline_keyboard=[]; 
	
	// Создаем клавиатуру
	for ($i=0;$i<count($text_array);$i++) {
		$button=array("text"=>"$text_array[$i]","$type_array[$i]"=>"$callback_array[$i]");
		$inline_keyboard[]=$button;
	}

	$test_keyboard[]=$inline_keyboard;

    $keyboard=array("inline_keyboard"=>$test_keyboard);
    $replyMarkup = json_encode($keyboard); 
	return $replyMarkup;
	
	}
	// ------------------------
	
	// Функция поиска в БД ключвого слова или тега
	function GetDBbyTag($tag) {
	
	$result=array(); // результат
	
	// Создаем запрос
	$query="SELECT * FROM messageTable WHERE
	tagCallback='$tag'";

	// Выполняем его
	$mysql_query=mysql_query($query);
	$num_result=mysql_num_rows($mysql_query); // Количество совпадений
	
	if ($num_result==1) {
		$result_array_sql=mysql_fetch_array($mysql_query);
		$result['status']="yes"; // успешно
		$result['type']=$result_array_sql['type']; // успешно
		$result['message']=$result_array_sql['message'];
		$result['buttonText']=$result_array_sql['buttonText'];
		$result['buttonCallback']=$result_array_sql['buttonCallback'];
		$result['buttonType']=$result_array_sql['buttonType'];
		
	} else {
		$result['status']="no"; // успешно
		$result['type']=""; // успешно
		$result['message']="";
		$result['buttonText']="";
		$result['buttonCallback']="";
		$result['buttonType']="";
	}
	
	return $result;
	
	}
	// -------------------
	
	// Метод отправки сообщения
	function SendMessage($chat_id, $message, $replyMarkup) {
		file_get_contents($this->api . '/sendMessage?chat_id=' . $chat_id . '&text=' . urlencode($message) . '&reply_markup=' . $replyMarkup);
	}
	// -------------------
	
}

$my_bot = new TelegramBot;

?>