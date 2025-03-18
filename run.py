from app import create_app

""" This file is the entry point for the application """

app = create_app()

if __name__ == "__main__":
    app.run(port=4000, debug=True)