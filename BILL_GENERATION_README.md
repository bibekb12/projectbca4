# Bill Generation Feature

## Overview
The bill generation feature allows for automatic HTML bill creation and storage for each sale transaction.

## Key Components
- `process_sale.php`: Generates and saves bill HTML
- `get_bill_file.php`: Retrieves saved bill files
- `sale.php`: Displays bill viewing and reprinting functionality

## Bill Storage
- Bills are saved in the `bills/` directory
- Filename format: `bill_[SALE_ID]_[TIMESTAMP].html`
- Bill path is stored in the `bill_file` column of the `sales` table

## Security Considerations
- Bills are saved with unique, non-guessable filenames
- Access to bill files is controlled via `get_bill_file.php`
- Requires active user session to view bills

## Database Schema Update
A new column `bill_file` was added to the `sales` table:
```sql
bill_file VARCHAR(255) NULL
```

## Workflow
1. Sale transaction is processed
2. Bill HTML is generated
3. Bill is saved to `bills/` directory
4. Bill path is saved in `sales` table
5. Users can view or reprint bills from recent transactions

## Troubleshooting
- Ensure `bills/` directory exists and is writable
- Check `sale_process_log.txt` for any bill generation errors
- Verify database connection and permissions

## Future Improvements
- Implement bill archiving
- Add bill search functionality
- Enhance bill template customization
