from app.utils.db_utils import get_cursor, close_cursor

class EventModel:
    def __init__(self):
        self.events = ()
        self.cur = get_cursor()
        
    def get_events(self):
        self.cur.execute("SELECT * FROM tevents ORDER BY id")
        self.events = self.cur.fetchall()
        close_cursor(self.cur)
        return self.events
    
    def get_event_with_id(self, id):
        self.cur.execute("SELECT * FROM tevents WHERE id = %s", (id,))
        event = self.cur.fetchone()
        close_cursor(self.cur)
        return event
    
    def get_filtered_events(self, start_date=None, end_date=None, type_id=None, budget=None):
        query = "SELECT * FROM tevents WHERE 1=1"
        params = []
        
        if start_date is not None:
            query += " AND date >= %s"
            params.append(start_date)
        
        if end_date is not None:
            query += " AND date <= %s"
            params.append(end_date)
        
        if type_id is not None:
            if len(type_id) > 1:
                values = ', '.join(['%s'] * len(type_id))
                query += f" AND type_id IN ({values})"
                params.extend(type_id)
            else:  # Si es un solo valor
                query += " AND type_id = %s"
                params.append(type_id)
        
            if budget is not None:
                query += " AND budget <= %s"
                params.append(budget)
        
        # Ejecutar la consulta con los parámetros
        self.cur.execute(query, tuple(params))
        self.events = self.cur.fetchall()
        close_cursor(self.cur)
        return self.events

    def get_events_by_date_range(self, start_date, end_date):
        return self.get_filtered_events(start_date=start_date, end_date=end_date)

    def get_events_by_type(self, type_id):
        return self.get_filtered_events(type_id=type_id)

    def get_events_by_budget(self, max_budget):
        return self.get_filtered_events(budget=max_budget)