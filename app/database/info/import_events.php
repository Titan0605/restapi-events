<?php
// Configuración de base de datos
$dbHost = 'localhost';
$dbUser = 'root'; // Usuario por defecto en XAMPP
$dbPass = '';     // Contraseña vacía por defecto en XAMPP
$dbName = 'events_db';

// Configuración de SerpApi
$apiKey = 'ec761a942d039c200d7e7d9ae855b61b65e4b369d71c755f57cb836dee577559'; // Necesitas reemplazar esto con tu clave API de SerpApi
$location = 'Chihuahua, Mexico';
$minEventsNeeded = 50; // Número mínimo de eventos que quieres obtener

// Función para conectar a la base de datos
function connectToDatabase($host, $user, $pass, $dbName) {
    $conn = new mysqli($host, $user, $pass, $dbName);
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Función para llamar a la API de SerpApi con diferentes consultas para obtener más eventos
function fetchMultipleEventsFromSerpApi($apiKey, $location, $minEvents = 50) {
    // Arreglo para almacenar todos los eventos
    $allEvents = [];
    
    // Lista de diferentes consultas para obtener variedad de eventos
    $queries = [
        'eventos en ' . $location,
        'conciertos en ' . $location,
        'festivales en ' . $location,
        'conferencias en ' . $location,
        'exposiciones en ' . $location,
        'teatro en ' . $location,
        'deportes en ' . $location,
        'ferias en ' . $location,
        'eventos culturales en ' . $location,
        'eventos educativos en ' . $location,
        'eventos gastronómicos en ' . $location,
        'eventos musicales en ' . $location
    ];
    
    // También hacemos búsquedas en inglés para obtener más variedad
    $englishQueries = [
        'events in ' . $location,
        'concerts in ' . $location,
        'festivals in ' . $location,
        'conferences in ' . $location
    ];
    
    // Combinar todas las consultas
    $allQueries = array_merge($queries, $englishQueries);
    
    // Hacer consultas hasta obtener suficientes eventos o agotar las consultas
    $eventsFound = 0;
    $processedIds = []; // Para evitar duplicados
    
    foreach ($allQueries as $query) {
        if ($eventsFound >= $minEvents) {
            break; // Ya tenemos suficientes eventos
        }
        
        $endpoint = "https://serpapi.com/search.json";
        $params = [
            'engine' => 'google_events',
            'q' => $query,
            'api_key' => $apiKey,
            'hl' => (strpos($query, 'in ') !== false) ? 'en' : 'es' // Idioma según la consulta
        ];
        
        $url = $endpoint . '?' . http_build_query($params);
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
            echo "Error en la llamada a la API con la consulta '$query': " . $err . "<br>";
            continue;
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['events_results']) && !empty($data['events_results'])) {
            echo "Encontrados " . count($data['events_results']) . " eventos para la consulta: '$query'<br>";
            
            // Agregar eventos no duplicados
            foreach ($data['events_results'] as $event) {
                $eventId = $event['title'] . '-' . (isset($event['date']) ? (is_array($event['date']) ? json_encode($event['date']) : $event['date']) : 'nodate');
                
                if (!isset($processedIds[$eventId])) {
                    $allEvents[] = $event;
                    $processedIds[$eventId] = true;
                    $eventsFound++;
                }
            }
        } else {
            echo "No se encontraron eventos para la consulta: '$query'<br>";
        }
        
        // Esperar un segundo entre consultas para no sobrecargar la API
        sleep(1);
    }
    
    // Si no tenemos suficientes eventos, generamos eventos ficticios adicionales
    if ($eventsFound < $minEvents) {
        $additionalEvents = generateFakeEvents($minEvents - $eventsFound, $location);
        $allEvents = array_merge($allEvents, $additionalEvents);
        echo "Se han añadido " . count($additionalEvents) . " eventos ficticios para complementar.<br>";
    }
    
    return ['events_results' => $allEvents];
}

// Función para generar eventos ficticios si no se encuentran suficientes reales
function generateFakeEvents($count, $location) {
    $fakeEvents = [];
    $eventTypes = [
        'Concierto', 'Festival', 'Conferencia', 'Exposición', 'Fiesta', 
        'Teatro', 'Evento Deportivo', 'Evento Cultural', 'Festival Gastronómico', 
        'Evento Educativo'
    ];
    
    $venues = [
        'Teatro de la Ciudad', 'Centro de Convenciones', 'Parque Central', 
        'Plaza Mayor', 'Universidad Autónoma de Chihuahua', 'Estadio Olímpico',
        'Casa Chihuahua', 'Quinta Gameros', 'Museo Casa Redonda', 'Parque El Palomar'
    ];
    
    $specificEvents = [
        'Festival Internacional de Chihuahua', 'Feria de Santa Rita', 
        'Maratón Internacional de Chihuahua', 'Expo Ganadera', 
        'Festival del Queso y Vino', 'Feria del Libro', 
        'Muestra Gastronómica Regional', 'Festival de Jazz', 
        'Encuentro de Danza Contemporánea', 'Concierto Sinfónica de Chihuahua'
    ];
    
    for ($i = 0; $i < $count; $i++) {
        // Determinar tipo de evento
        $eventTypeIndex = array_rand($eventTypes);
        $eventType = $eventTypes[$eventTypeIndex];
        
        // Crear nombre de evento
        if (rand(0, 1) == 0 && !empty($specificEvents)) {
            $eventIndex = array_rand($specificEvents);
            $eventName = $specificEvents[$eventIndex];
            // Eliminar para no repetir
            unset($specificEvents[$eventIndex]);
            $specificEvents = array_values($specificEvents);
        } else {
            $eventName = $eventType . ' ' . substr(md5(rand()), 0, 5);
        }
        
        // Crear fecha (dentro de los próximos 6 meses)
        $daysInFuture = rand(1, 180);
        $eventDate = date('Y-m-d', strtotime("+$daysInFuture days"));
        
        // Seleccionar venue
        $venue = $venues[array_rand($venues)];
        
        // Crear evento ficticio
        $fakeEvent = [
            'title' => $eventName,
            'date' => $eventDate,
            'time' => sprintf('%02d:%02d %s', rand(1, 12), rand(0, 1) * 30, rand(0, 1) ? 'AM' : 'PM'),
            'address' => [$venue, 'Chihuahua, Mexico'],
            'description' => "Un $eventType imperdible en Chihuahua. Te esperamos en $venue para disfrutar de este gran evento.",
            'thumbnail' => 'https://example.com/fake-event-' . rand(1, 20) . '.jpg',
            'is_fake' => true // Marcamos como ficticio para saber que fue generado
        ];
        
        $fakeEvents[] = $fakeEvent;
    }
    
    return $fakeEvents;
}

// Función para procesar los eventos y guardarlos en la base de datos
function processAndSaveEvents($events, $conn) {
    // Contador de eventos importados
    $importedCount = 0;
    
    foreach ($events['events_results'] as $event) {
        // Extraer la información del evento
        $name = $event['title'] ?? 'Evento sin título';
        
        // Procesar fecha (manejar tanto string como array)
        $dateInfo = $event['date'] ?? '';
        if (is_array($dateInfo)) {
            // Si es un array, intentamos obtener una fecha válida
            if (isset($dateInfo['start_date'])) {
                $date = $dateInfo['start_date'];
            } else {
                // Convertir a string y extraer fecha
                $dateStr = json_encode($dateInfo);
                preg_match('/\d{4}-\d{2}-\d{2}/', $dateStr, $matches);
                $date = !empty($matches) ? $matches[0] : date('Y-m-d', strtotime('+' . rand(1, 180) . ' days'));
            }
        } else {
            // Si es un string, intentar convertirlo directamente
            try {
                $dateObj = new DateTime($dateInfo);
                $date = $dateObj->format('Y-m-d');
            } catch (Exception $e) {
                // Si hay error, asignar una fecha aleatoria futura
                $date = date('Y-m-d', strtotime('+' . rand(1, 180) . ' days'));
            }
        }
        
        // Procesar hora
        $timeInfo = $event['time'] ?? '';
        if (!empty($timeInfo)) {
            // Intentar extraer la hora
            if (preg_match('/(\d{1,2}):(\d{2})\s*(AM|PM)/i', $timeInfo, $matches)) {
                $hour = (int)$matches[1];
                if (strtoupper($matches[3]) === 'PM' && $hour < 12) {
                    $hour += 12;
                } elseif (strtoupper($matches[3]) === 'AM' && $hour === 12) {
                    $hour = 0;
                }
                $minute = (int)$matches[2];
                $time = sprintf('%02d:%02d:00', $hour, $minute);
            } else {
                $time = '12:00:00'; // Tiempo por defecto
            }
        } else {
            $time = sprintf('%02d:%02d:00', rand(9, 20), rand(0, 59)); // Hora aleatoria entre 9am y 8pm
        }
        
        // Determinar tipo de evento
        $eventType = determineEventType($name);
        
        // Descripción
        $description = $event['description'] ?? ($event['is_fake'] ?? false ? $event['description'] : 'Sin descripción disponible');
        
        // Imagen
        $image = $event['thumbnail'] ?? '';
        
        // Dirección
        $address = $event['address'] ?? [];
        $addressText = is_array($address) ? implode(', ', $address) : $address;
        if (empty($addressText)) {
            $addressText = 'Chihuahua, Chihuahua, México';
        }
        
        // Generar coordenadas aproximadas para Chihuahua
        $lat = 28.6353 + (rand(-100, 100) / 1000); // Valor base + pequeña variación
        $lng = -106.0889 + (rand(-100, 100) / 1000);
        
        // Obtener id de ubicación o crear una nueva
        $locationId = getOrCreateLocation($conn, $addressText, $lat, $lng);
        
        // Obtener id de organizador o asignar uno al azar
        $organizerId = getRandomOrganizer($conn);
        
        // Generar un presupuesto aproximado
        $budget = rand(0, 5000);
        
        // Determinar si es destacado (20% de probabilidad)
        $featured = (rand(1, 5) == 1) ? 1 : 0;
        
        // Insertar en la base de datos
        $stmt = $conn->prepare("INSERT INTO tevents (name, date, time, type_id, budget, description, image, location_id, organizer_id, featured) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("sssidssiis", $name, $date, $time, $eventType, $budget, $description, $image, $locationId, $organizerId, $featured);
        
        if ($stmt->execute()) {
            $importedCount++;
            echo "Evento importado: " . $name . " - " . $date . "<br>";
        } else {
            echo "Error al importar evento: " . $stmt->error . "<br>";
        }
        
        $stmt->close();
    }
    
    return $importedCount;
}

// Función para determinar el tipo de evento basado en su nombre
function determineEventType($eventName) {
    $eventName = strtolower($eventName);
    
    $keywords = [
        1 => ['concierto', 'música', 'sinfónica', 'banda', 'recital', 'concert', 'music'],
        2 => ['festival', 'feria', 'feria', 'fair'],
        3 => ['conferencia', 'charla', 'seminario', 'congreso', 'conference', 'seminar'],
        4 => ['exposición', 'exhibición', 'muestra', 'galería', 'exhibition', 'gallery'],
        5 => ['fiesta', 'celebración', 'baile', 'party', 'celebration', 'dance'],
        6 => ['teatro', 'obra', 'drama', 'comedia', 'theater', 'play', 'drama'],
        7 => ['deporte', 'maratón', 'carrera', 'torneo', 'partido', 'sport', 'marathon', 'game'],
        8 => ['cultural', 'tradición', 'folklore', 'culture', 'tradition'],
        9 => ['gastronómico', 'comida', 'degustación', 'culinario', 'food', 'tasting', 'culinary'],
        10 => ['educativo', 'taller', 'curso', 'aprendizaje', 'educational', 'workshop', 'course']
    ];
    
    foreach ($keywords as $typeId => $typeKeywords) {
        foreach ($typeKeywords as $keyword) {
            if (strpos($eventName, $keyword) !== false) {
                return $typeId;
            }
        }
    }
    
    // Si no se encuentra correspondencia, asignar un tipo aleatorio
    return rand(1, 10);
}

// Función para obtener o crear una ubicación
function getOrCreateLocation($conn, $address, $lat, $lng) {
    // Verificar si la ubicación ya existe
    $stmt = $conn->prepare("SELECT id FROM tlocations WHERE address = ?");
    $stmt->bind_param("s", $address);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row['id'];
    }
    
    // Si no existe, crear nueva ubicación
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO tlocations (address, lat, lng) VALUES (?, ?, ?)");
    $stmt->bind_param("sdd", $address, $lat, $lng);
    $stmt->execute();
    $locationId = $conn->insert_id;
    $stmt->close();
    
    return $locationId;
}

// Función para obtener un organizador aleatorio
function getRandomOrganizer($conn) {
    $result = $conn->query("SELECT id FROM torganizers ORDER BY RAND() LIMIT 1");
    if ($row = $result->fetch_assoc()) {
        return $row['id'];
    }
    
    // Si no hay organizadores, devolver 1 (asumiendo que el ID 1 existe)
    return 1;
}

// Ejecución principal
echo "<h1>Importador de Eventos SerpApi a MySQL</h1>";

try {
    // Conectar a la base de datos
    $conn = connectToDatabase($dbHost, $dbUser, $dbPass, $dbName);
    echo "Conexión a la base de datos establecida.<br>";
    
    // Obtener eventos de SerpApi (múltiples consultas)
    echo "Obteniendo eventos de SerpApi para " . $location . "...<br>";
    $eventsData = fetchMultipleEventsFromSerpApi($apiKey, $location, $minEventsNeeded);
    
    if (isset($eventsData['events_results']) && !empty($eventsData['events_results'])) {
        $totalEvents = count($eventsData['events_results']);
        echo "Se encontraron un total de " . $totalEvents . " eventos únicos.<br>";
        
        // Procesar y guardar eventos
        echo "Procesando eventos...<br>";
        $importedCount = processAndSaveEvents($eventsData, $conn);
        
        echo "<h2>Resumen:</h2>";
        echo "Total de eventos encontrados/generados: " . $totalEvents . "<br>";
        echo "Eventos importados correctamente: " . $importedCount . "<br>";
    } else {
        echo "No se encontraron eventos o hubo un error en la respuesta de la API.<br>";
        if (isset($eventsData['error'])) {
            echo "Error: " . $eventsData['error'] . "<br>";
        }
    }
    
    // Cerrar conexión
    $conn->close();
    
} catch (Exception $e) {
    echo "Ha ocurrido un error: " . $e->getMessage();
}
?>