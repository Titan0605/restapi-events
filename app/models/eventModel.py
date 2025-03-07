from app.utils.db_utils import get_cursor, commit_changes, close_cursor

class EventModel:
    def __init__(self):
        self.events = ()
        self.cur = get_cursor()
        
    def get_events(self):
        self.cur.execute("SELECT * FROM tevents")
        self.events = self.cur.fetchall()
        return self.events
    
    def get_event_with_id(self, id):
        self.cur.execute("SELECT * FROM tevents WHERE id = %s", (id,))
        