import { computed, ref, watch } from "vue";
import { Question } from "@/types";
import { Answer } from "./types";

type Q = Omit<Question, "correct_option">;

export function useQuizAnswerManager(
    key: string,
    initialQuestions: Q[] = [],
    initialAnswers?: Answer[],
) {
    const ansPersitKey = ref(`lesson:answers:${key}`);
    const lastQuestPersitKey = ref(`lesson:question:${key}`);
    const currentQuestionIdx = ref(
        retrieveLastQuestion()
            ? initialQuestions.findIndex((i) => retrieveLastQuestion() === i.id)
            : 0,
    );
    const questions = ref<Array<Q>>(initialQuestions);
    const answers = ref(
        initialAnswers
            ? new Map(initialAnswers.map((obj) => [obj.question_id, obj]))
            : retrieveAnswers() ?? new Map<Answer["question_id"], Answer>(),
    );

    const nextQuestion = () => {
        currentQuestionIdx.value++;
    };
    const previousQuestion = () => {
        currentQuestionIdx.value--;
    };

    const answerQuestion = (
        question_id: Answer["question_id"],
        selected_option: Answer["selected_option"],
    ) => {
        answers.value.set(question_id, {
            question_id,
            selected_option,
        });
    };

    function persistAnswers(value: typeof answers.value) {
        localStorage.setItem(
            ansPersitKey.value,
            JSON.stringify(Array.from(value.entries())),
        );
    }

    function retrieveAnswers() {
        if (!localStorage) return null;

        const v = localStorage.getItem(ansPersitKey.value);
        const parsed = v
            ? new Map<Answer["question_id"], Answer>(JSON.parse(v))
            : null;

        return parsed;
    }

    function persistLastQuestion(value: string | number) {
        localStorage.setItem(lastQuestPersitKey.value, JSON.stringify(value));
    }

    function retrieveLastQuestion() {
        if (!localStorage) return null;

        const v = localStorage.getItem(lastQuestPersitKey.value);

        const parsed = v ? JSON.parse(v) : null;
        return parsed;
    }

    const hasSelectedAnswer = computed(() =>
        answers.value.has(currentQuesion.value.id),
    );
    const hasNextQuesion = computed(
        () => currentQuestionIdx.value < questions.value.length - 1,
    );
    const hasPreviousQuesion = computed(() => currentQuestionIdx.value > 0);

    const currentQuesion = computed(() => {
        return questions.value[currentQuestionIdx.value];
    });

    const currentAnswer = computed(() => {
        // const currentQuestionId = currentQuesion.value.id;
        const newAnswer = answers.value.get(
            currentQuesion.value.id,
        )?.selected_option;
        return newAnswer;
    });

    watch(
        answers,
        (newValue) => {
            persistAnswers(newValue);
        },
        { deep: true },
    );

    watch(
        currentQuesion,
        (newValue) => {
            persistLastQuestion(newValue.id);
        },
        { deep: true },
    );

    return {
        currentQuestionIdx,
        currentQuesion,
        currentAnswer,
        nextQuestion,
        previousQuestion,
        answerQuestion,
        hasSelectedAnswer,
        hasNextQuesion,
        hasPreviousQuesion,
        answers,
    };
}
