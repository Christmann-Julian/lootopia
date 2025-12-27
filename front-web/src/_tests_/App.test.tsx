import { render, screen } from "@testing-library/react";
import App from "../App";

describe("App component", () => {
  it('affiche le texte "welcome"', () => {
    render(<App />);
    expect(screen.getByText("welcome")).toBeInTheDocument();
  });
});
