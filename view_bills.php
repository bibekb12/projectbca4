<?php

// Update the main query
$query = "SELECT s.*, c.name as customer_name, c.contact, u.username 
          FROM sales s 
          JOIN customers c ON s.customer_id = c.id 
          JOIN users u ON s.user_id = u.id 
          WHERE s.status = 'Y'"; 