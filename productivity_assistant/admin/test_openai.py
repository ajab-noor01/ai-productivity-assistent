import os
from dotenv import load_dotenv
from openai import OpenAI

load_dotenv()

client = OpenAI(api_key=os.getenv("OPENAI_API_KEY"))

try:
    response = client.responses.create(
        model="gpt-5-mini",
        input="Say hello to my AI Productivity Assistant in one short sentence."
    )

    print("SUCCESS: OpenAI API connected")
    print("AI RESPONSE:")
    print(response.output_text)

except Exception as e:
    print("ERROR:")
    print(e)